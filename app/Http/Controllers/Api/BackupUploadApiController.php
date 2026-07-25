<?php

namespace App\Http\Controllers\Api;

use App\Domain\Backups\AgentBackupUploadService;
use App\Domain\Backups\BackupRequestHmacVerifier;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BackupUploadApiController extends Controller
{
    public function __construct(
        private readonly BackupRequestHmacVerifier $hmac,
        private readonly AgentBackupUploadService $uploads,
    ) {}

    public function createSession(Request $request): JsonResponse
    {
        $project = $this->project($request);
        $this->hmac->verify($request, $project);

        $data = $request->validate([
            'agent_key' => ['required', 'string', 'max:191'],
            'tenant_key' => ['nullable', 'string', 'max:191'],
            'product_key' => ['nullable', 'string', 'max:64'],
            'name' => ['nullable', 'string', 'max:255'],
            'backup_type' => ['nullable', 'string', 'max:64'],
            'artifact_name' => ['required', 'string', 'max:255'],
            'checksum' => ['required', 'string', 'max:128'],
            'size_bytes' => ['required', 'integer', 'min:1'],
            'content_type' => ['nullable', 'string', 'max:128'],
            'environment' => ['nullable', 'string', 'max:64'],
            'manifest_hash' => ['nullable', 'string', 'max:128'],
            'retention_policy' => ['nullable', 'string', 'max:64'],
            'local_job_id' => ['nullable'],
        ]);

        try {
            $session = $this->uploads->createSession($project, $data, $request);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($session, 201);
    }

    public function putBytes(Request $request, string $uploadId): JsonResponse
    {
        try {
            $result = $this->uploads->receivePut($uploadId, $request);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        if (! ($result['ok'] ?? false)) {
            return response()->json(['message' => $result['message'] ?? 'Upload failed.'], 422);
        }

        return response()->json([
            'ok' => true,
            'bytes' => $result['bytes'] ?? null,
        ]);
    }

    public function complete(Request $request): JsonResponse
    {
        $project = $this->project($request);
        $this->hmac->verify($request, $project);

        $data = $request->validate([
            'upload_id' => ['required', 'string', 'max:64'],
            'upload_token' => ['required', 'string', 'max:128'],
            'backup_id' => ['nullable', 'integer'],
            'checksum' => ['required', 'string', 'max:128'],
            'size_bytes' => ['required', 'integer', 'min:1'],
            'object_key' => ['nullable', 'string', 'max:512'],
            'manifest_hash' => ['nullable', 'string', 'max:128'],
            'artifact_name' => ['nullable', 'string', 'max:255'],
            'retention_policy' => ['nullable', 'string', 'max:64'],
            'manifest' => ['nullable', 'array'],
            'tenant_key' => ['nullable', 'string', 'max:191'],
            'product_key' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            $payload = $this->uploads->complete($project, $data, $request);
        } catch (RuntimeException $e) {
            $status = str_contains(strtolower($e->getMessage()), 'not found') ? 404 : 422;

            return response()->json(['message' => $e->getMessage()], $status);
        }

        return response()->json($payload);
    }

    public function failed(Request $request): JsonResponse
    {
        $project = $this->project($request);
        $this->hmac->verify($request, $project);

        $data = $request->validate([
            'upload_id' => ['required', 'string', 'max:64'],
            'upload_token' => ['nullable', 'string', 'max:128'],
            'backup_id' => ['nullable', 'integer'],
            'reason' => ['nullable', 'string', 'max:500'],
            'tenant_key' => ['nullable', 'string', 'max:191'],
            'product_key' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            $payload = $this->uploads->failed($project, $data, $request);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($payload);
    }

    public function status(Request $request, int $id): JsonResponse
    {
        $project = $this->project($request);
        $this->hmac->verify($request, $project);

        try {
            return response()->json($this->uploads->status($project, $id));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (HttpException $e) {
            throw $e;
        }
    }

    public function retention(Request $request, int $id): JsonResponse
    {
        $project = $this->project($request);
        $this->hmac->verify($request, $project);

        try {
            return response()->json($this->uploads->retention($project, $id));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    private function project(Request $request): Project
    {
        $project = $request->attributes->get('licensed_project');

        if (! $project instanceof Project) {
            abort(500, 'Project context missing.');
        }

        return $project;
    }
}
