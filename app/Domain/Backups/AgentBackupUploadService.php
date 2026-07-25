<?php

namespace App\Domain\Backups;

use App\Models\Backup;
use App\Models\BackupAgent;
use App\Models\Project;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Lean agent upload pipeline: session → PUT relay → verify → catalog → retain N.
 */
final class AgentBackupUploadService
{
    public function __construct(
        private readonly BackupActionLogger $actions,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createSession(Project $project, array $data, ?Request $request = null): array
    {
        $cfg = $this->config();
        $size = (int) ($data['size_bytes'] ?? 0);
        $max = (int) $cfg['max_bytes'];

        if ($size <= 0 || $size > $max) {
            throw new RuntimeException(sprintf('Artifact size must be between 1 and %d bytes.', $max));
        }

        $agentKey = (string) $data['agent_key'];
        $agent = BackupAgent::query()
            ->where('agent_key', $agentKey)
            ->where('hosted_project_id', $project->id)
            ->first();

        if (! $agent) {
            throw new RuntimeException('Agent not registered.');
        }

        $tenant = $this->resolveTenant($project, $data['tenant_key'] ?? null);
        $uploadId = (string) Str::ulid();
        $uploadToken = Str::random(64);
        $ttlMinutes = max(5, (int) $cfg['session_ttl_minutes']);
        $expiresAt = now()->addMinutes($ttlMinutes);
        $objectKey = sprintf(
            '%s/%s/%s/%s',
            rtrim((string) $cfg['path_prefix'], '/'),
            $agentKey,
            $uploadId,
            basename((string) ($data['artifact_name'] ?? 'backup.zip'))
        );

        $backup = Backup::query()->create(Backup::attributesWithHostedProject($project->id, [
            'name' => (string) ($data['name'] ?? ('Agent backup '.$agentKey)),
            'tenant_id' => $tenant?->id ?? $agent->tenant_id,
            'backup_type' => $this->normalizeType((string) ($data['backup_type'] ?? 'full')),
            'size_bytes' => $size,
            'started_at' => now(),
            'status' => 'running',
            'storage_disk' => (string) $cfg['disk'],
            'archive_path' => $objectKey,
            'archive_filename' => basename($objectKey),
            'checksum' => (string) ($data['checksum'] ?? ''),
            'integrity_verified' => false,
            'is_restore_point' => false,
            'notes' => json_encode([
                'source' => 'agent_upload',
                'agent_key' => $agentKey,
                'upload_id' => $uploadId,
                'local_job_id' => $data['local_job_id'] ?? null,
                'retention_policy' => $data['retention_policy'] ?? 'keep_5',
                'environment' => $data['environment'] ?? null,
                'manifest_hash' => $data['manifest_hash'] ?? null,
            ], JSON_UNESCAPED_SLASHES),
        ]));

        $session = [
            'upload_id' => $uploadId,
            'upload_token' => $uploadToken,
            'backup_id' => $backup->id,
            'object_key' => $objectKey,
            'checksum' => (string) ($data['checksum'] ?? ''),
            'size_bytes' => $size,
            'agent_key' => $agentKey,
            'hosted_project_id' => $project->id,
            'expires_at' => $expiresAt->toIso8601String(),
            'put_received' => false,
            'bytes_received' => 0,
            'retention_policy' => (string) ($data['retention_policy'] ?? 'keep_5'),
        ];

        Cache::put($this->cacheKey($uploadId), $session, $expiresAt);

        $this->actions->log($backup, 'agent.upload_session', null, [
            'upload_id' => $uploadId,
            'agent_key' => $agentKey,
            'size_bytes' => $size,
        ], 'success', $request);

        return [
            'upload_id' => $uploadId,
            'upload_token' => $uploadToken,
            'backup_id' => $backup->id,
            'object_key' => $objectKey,
            'upload_url' => url('/api/v1/backups/upload/'.$uploadId),
            'upload_expiry' => $expiresAt->toIso8601String(),
            'upload_headers' => [
                'Content-Type' => (string) ($data['content_type'] ?? 'application/zip'),
            ],
            'storage_provider' => (string) $cfg['disk'],
            'max_bytes' => $max,
        ];
    }

    /**
     * @return array{ok: bool, message: string, bytes?: int}
     */
    public function receivePut(string $uploadId, Request $request): array
    {
        $session = $this->sessionOrFail($uploadId);
        $token = (string) $request->header('X-Backup-Upload-Token', '');

        if ($token === '' || ! hash_equals((string) $session['upload_token'], $token)) {
            return ['ok' => false, 'message' => 'Invalid upload token.'];
        }

        if (now()->greaterThan(\Carbon\Carbon::parse((string) $session['expires_at']))) {
            return ['ok' => false, 'message' => 'Upload session expired.'];
        }

        $expected = (int) $session['size_bytes'];
        $disk = (string) $this->config()['disk'];
        $objectKey = (string) $session['object_key'];

        Storage::disk($disk)->makeDirectory(dirname($objectKey));

        $tmp = tempnam(sys_get_temp_dir(), 'bakput_');
        if ($tmp === false) {
            return ['ok' => false, 'message' => 'Unable to stage upload.'];
        }

        $bytes = 0;
        $max = (int) $this->config()['max_bytes'];

        try {
            $in = $this->openUploadStream($request);
            if ($in === false) {
                @unlink($tmp);

                return ['ok' => false, 'message' => 'Unable to read upload body.'];
            }

            $out = fopen($tmp, 'wb');
            if ($out === false) {
                if (is_resource($in)) {
                    fclose($in);
                }
                @unlink($tmp);

                return ['ok' => false, 'message' => 'Unable to stage upload.'];
            }

            try {
                while (! feof($in)) {
                    $chunk = fread($in, 1024 * 1024);
                    if ($chunk === false || $chunk === '') {
                        break;
                    }
                    $bytes += strlen($chunk);
                    if ($bytes > $max) {
                        return ['ok' => false, 'message' => 'Upload exceeds max size.'];
                    }
                    fwrite($out, $chunk);
                }
            } finally {
                if (is_resource($in)) {
                    fclose($in);
                }
                fclose($out);
            }
        } catch (\Throwable $e) {
            @unlink($tmp);

            return ['ok' => false, 'message' => 'Upload read failed: '.$e->getMessage()];
        }

        if ($bytes !== $expected) {
            @unlink($tmp);

            return [
                'ok' => false,
                'message' => sprintf('Size mismatch: expected %d bytes, received %d.', $expected, $bytes),
                'bytes' => $bytes,
            ];
        }

        $stream = fopen($tmp, 'rb');
        if ($stream === false) {
            @unlink($tmp);

            return ['ok' => false, 'message' => 'Unable to open staged upload.'];
        }

        try {
            Storage::disk($disk)->writeStream($objectKey, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
            @unlink($tmp);
        }

        $session['put_received'] = true;
        $session['bytes_received'] = $bytes;
        Cache::put($this->cacheKey($uploadId), $session, \Carbon\Carbon::parse((string) $session['expires_at']));

        return ['ok' => true, 'message' => 'received', 'bytes' => $bytes];
    }

    /**
     * @return resource|false
     */
    private function openUploadStream(Request $request)
    {
        // Live PUTs: stream php://input (avoids buffering whole zip in memory).
        // PHPUnit injects content and leaves php://input empty.
        if (! app()->runningUnitTests()) {
            $in = @fopen('php://input', 'rb');
            if ($in !== false) {
                return $in;
            }
        }

        $content = $request->getContent();
        if (is_string($content) && $content !== '') {
            $tmp = fopen('php://temp', 'w+b');
            if ($tmp === false) {
                return false;
            }
            fwrite($tmp, $content);
            rewind($tmp);

            return $tmp;
        }

        return fopen('php://input', 'rb');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function complete(Project $project, array $data, ?Request $request = null): array
    {
        $uploadId = (string) ($data['upload_id'] ?? '');
        $session = $this->sessionOrFail($uploadId);

        if ((int) $session['hosted_project_id'] !== (int) $project->id) {
            throw new RuntimeException('Upload session does not belong to this project.');
        }

        $token = (string) ($data['upload_token'] ?? '');
        if ($token === '' || ! hash_equals((string) $session['upload_token'], $token)) {
            throw new RuntimeException('Invalid upload token.');
        }

        $backup = Backup::query()->findOrFail((int) $session['backup_id']);
        $disk = (string) ($backup->storage_disk ?: $this->config()['disk']);
        $objectKey = (string) ($session['object_key'] ?: $backup->archive_path);
        $expectedChecksum = strtolower((string) ($data['checksum'] ?? $session['checksum'] ?? ''));
        $expectedSize = (int) ($data['size_bytes'] ?? $session['size_bytes'] ?? 0);

        if (! Storage::disk($disk)->exists($objectKey)) {
            $backup->forceFill(['status' => 'failed', 'completed_at' => now()])->save();
            $this->actions->log($backup, 'agent.upload_verify_failed', null, [
                'reason' => 'object_missing',
            ], 'failure', $request);

            return $this->completePayload($backup, false, 'Uploaded object missing on Dashboard storage.');
        }

        $absolute = Storage::disk($disk)->path($objectKey);
        $actualSize = is_file($absolute) ? (int) filesize($absolute) : (int) Storage::disk($disk)->size($objectKey);
        $actualChecksum = is_file($absolute)
            ? strtolower((string) hash_file('sha256', $absolute))
            : '';

        $passed = $actualSize === $expectedSize
            && $expectedChecksum !== ''
            && hash_equals($expectedChecksum, $actualChecksum);

        if (! $passed) {
            $backup->forceFill([
                'status' => 'failed',
                'completed_at' => now(),
                'size_bytes' => $actualSize,
                'checksum' => $actualChecksum ?: $backup->checksum,
                'integrity_verified' => false,
            ])->save();

            $this->actions->log($backup, 'agent.upload_verify_failed', null, [
                'expected_checksum' => $expectedChecksum,
                'actual_checksum' => $actualChecksum,
                'expected_size' => $expectedSize,
                'actual_size' => $actualSize,
            ], 'failure', $request);

            Cache::forget($this->cacheKey($uploadId));

            return $this->completePayload(
                $backup,
                false,
                sprintf(
                    'Remote verification failed (size %d/%d, checksum match=%s).',
                    $actualSize,
                    $expectedSize,
                    hash_equals($expectedChecksum, $actualChecksum) ? 'yes' : 'no'
                )
            );
        }

        $started = $backup->started_at;
        $backup->forceFill([
            'status' => 'successful',
            'completed_at' => now(),
            'duration_seconds' => $started ? max(0, $started->diffInSeconds(now())) : null,
            'size_bytes' => $actualSize,
            'checksum' => $actualChecksum,
            'archive_path' => $objectKey,
            'archive_filename' => basename($objectKey),
            'integrity_verified' => true,
            'is_restore_point' => true,
        ])->save();

        $retention = $this->enforceRetention((int) $project->id);

        $this->actions->log($backup, 'agent.upload_completed', null, [
            'upload_id' => $uploadId,
            'checksum' => $actualChecksum,
            'size_bytes' => $actualSize,
            'retention_deleted' => $retention['deleted_ids'],
        ], 'success', $request);

        Cache::forget($this->cacheKey($uploadId));

        return $this->completePayload($backup->fresh(), true, 'Remote verification passed.', $retention);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function failed(Project $project, array $data, ?Request $request = null): array
    {
        $uploadId = (string) ($data['upload_id'] ?? '');
        $session = Cache::get($this->cacheKey($uploadId));

        if (! is_array($session) || (int) ($session['hosted_project_id'] ?? 0) !== (int) $project->id) {
            return ['accepted' => true, 'message' => 'Session already gone.'];
        }

        $token = (string) ($data['upload_token'] ?? '');
        if ($token !== '' && ! hash_equals((string) $session['upload_token'], $token)) {
            throw new RuntimeException('Invalid upload token.');
        }

        $backup = Backup::query()->find((int) $session['backup_id']);
        if ($backup) {
            $meta = json_decode((string) $backup->notes, true);
            if (! is_array($meta)) {
                $meta = ['raw_notes' => $backup->notes];
            }
            $meta['upload_failed_reason'] = (string) ($data['reason'] ?? 'unknown');
            $meta['failed_at'] = now()->toIso8601String();

            $backup->forceFill([
                'status' => 'failed',
                'completed_at' => now(),
                'notes' => json_encode($meta, JSON_UNESCAPED_SLASHES),
            ])->save();

            $this->actions->log($backup, 'agent.upload_failed', null, [
                'upload_id' => $uploadId,
                'reason' => $data['reason'] ?? null,
            ], 'failure', $request);

            $disk = (string) ($backup->storage_disk ?: $this->config()['disk']);
            $path = (string) ($backup->archive_path ?? '');
            if ($path !== '' && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }

        Cache::forget($this->cacheKey($uploadId));

        return ['accepted' => true, 'backup_id' => $backup?->id];
    }

    /**
     * @return array<string, mixed>
     */
    public function status(Project $project, int $backupId): array
    {
        $backup = $this->projectBackup($project, $backupId);

        return [
            'backup' => $this->backupArray($backup),
            'status' => $backup->status,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retention(Project $project, int $backupId): array
    {
        $backup = $this->projectBackup($project, $backupId);
        $retain = max(1, (int) $this->config()['retain']);
        $meta = $this->decodeNotes($backup->notes);

        return [
            'backup_id' => $backup->id,
            'policy' => $meta['retention_policy'] ?? 'keep_'.$retain,
            'retain' => $retain,
            'status' => $backup->status,
            'assigned' => $backup->status === 'successful',
            'storage_disk' => $backup->storage_disk,
            'object_key' => $backup->archive_path,
            'checksum' => $backup->checksum,
            'size_bytes' => $backup->size_bytes,
            'completed_at' => $backup->completed_at?->toIso8601String(),
        ];
    }

    /**
     * @return array{deleted_ids: list<int>, kept: int, retain: int}
     */
    public function enforceRetention(int $hostedProjectId): array
    {
        $retain = max(1, (int) $this->config()['retain']);
        $prefix = rtrim((string) $this->config()['path_prefix'], '/').'/';

        $rows = Backup::query()
            ->where('hosted_project_id', $hostedProjectId)
            ->where('status', 'successful')
            ->where('archive_path', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->get();

        $keep = $rows->take($retain);
        $drop = $rows->slice($retain);
        $deleted = [];

        foreach ($drop as $old) {
            /** @var Backup $old */
            $disk = (string) ($old->storage_disk ?: $this->config()['disk']);
            $path = (string) ($old->archive_path ?? '');
            if ($path !== '' && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
            $deleted[] = (int) $old->id;
            $old->delete();
        }

        return [
            'deleted_ids' => $deleted,
            'kept' => $keep->count(),
            'retain' => $retain,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function config(): array
    {
        return (array) config('backups.agent_upload', []);
    }

    private function cacheKey(string $uploadId): string
    {
        return 'backup_agent_upload:'.$uploadId;
    }

    /**
     * @return array<string, mixed>
     */
    private function sessionOrFail(string $uploadId): array
    {
        $session = Cache::get($this->cacheKey($uploadId));
        if (! is_array($session)) {
            throw new RuntimeException('Upload session not found or expired.');
        }

        return $session;
    }

    private function projectBackup(Project $project, int $backupId): Backup
    {
        $backup = Backup::query()
            ->whereKey($backupId)
            ->where('hosted_project_id', $project->id)
            ->first();

        if (! $backup) {
            throw new RuntimeException('Backup not found.');
        }

        return $backup;
    }

    private function normalizeType(string $type): string
    {
        $allowed = ['full', 'database', 'files', 'snapshot', 'incremental'];

        return in_array($type, $allowed, true) ? $type : 'full';
    }

    private function resolveTenant(Project $project, mixed $tenantKey): ?Tenant
    {
        if (! is_string($tenantKey) || $tenantKey === '') {
            return null;
        }

        return Tenant::query()
            ->where('tenant_key', $tenantKey)
            ->where('hosted_project_id', $project->id)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeNotes(?string $notes): array
    {
        if (! is_string($notes) || $notes === '') {
            return [];
        }

        $decoded = json_decode($notes, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>|null  $retention
     * @return array<string, mixed>
     */
    private function completePayload(Backup $backup, bool $passed, string $notes, ?array $retention = null): array
    {
        return [
            'passed' => $passed,
            'verification' => [
                'passed' => $passed,
                'checksum' => $backup->checksum,
                'size_bytes' => $backup->size_bytes,
                'notes' => $notes,
            ],
            'backup' => $this->backupArray($backup),
            'retention' => $retention,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function backupArray(Backup $backup): array
    {
        return [
            'id' => $backup->id,
            'status' => $backup->status === 'successful' ? 'completed' : $backup->status,
            'name' => $backup->name,
            'backup_type' => $backup->backup_type,
            'size_bytes' => $backup->size_bytes,
            'checksum' => $backup->checksum,
            'storage_disk' => $backup->storage_disk,
            'storage_provider' => $backup->storage_disk,
            'archive_path' => $backup->archive_path,
            'integrity_verified' => (bool) $backup->integrity_verified,
            'completed_at' => $backup->completed_at?->toIso8601String(),
        ];
    }
}
