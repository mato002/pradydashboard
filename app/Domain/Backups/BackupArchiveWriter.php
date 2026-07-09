<?php

namespace App\Domain\Backups;

use App\Models\Backup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

final class BackupArchiveWriter
{
    public function writeSimulatedArchive(Backup $backup): string
    {
        $disk = (string) ($backup->storage_disk ?: 'local');
        $relativePath = 'backups/'.$backup->id.'/'.Str::slug($backup->name ?: 'backup').'-'.$backup->id.'.zip';
        $filename = basename($relativePath);

        Storage::disk($disk)->makeDirectory('backups/'.$backup->id);

        $absolutePath = Storage::disk($disk)->path($relativePath);
        $this->createZip($absolutePath, $backup);

        $backup->forceFill([
            'archive_path' => $relativePath,
            'archive_filename' => $filename,
            'checksum' => hash_file('sha256', $absolutePath) ?: null,
            'size_bytes' => filesize($absolutePath) ?: $backup->size_bytes,
        ])->save();

        return $relativePath;
    }

    private function createZip(string $absolutePath, Backup $backup): void
    {
        $zip = new ZipArchive;
        if ($zip->open($absolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create backup archive.');
        }

        $manifest = json_encode([
            'backup_id' => $backup->id,
            'name' => $backup->name,
            'backup_type' => $backup->backup_type,
            'generated_at' => now()->toIso8601String(),
            'tenant_id' => $backup->tenant_id,
            'server_id' => $backup->server_id,
            'notes' => $backup->notes,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $zip->addFromString('manifest.json', (string) $manifest);
        $zip->addFromString('README.txt', "Prady Dashboard backup archive\nBackup #{$backup->id}\n");
        $zip->close();
    }
}
