<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Agent upload ingest (MFI Backup Agent → Dashboard catalog)
    |--------------------------------------------------------------------------
    */

    'agent_upload' => [
        'disk' => env('BACKUP_AGENT_UPLOAD_DISK', 'local'),
        'session_ttl_minutes' => (int) env('BACKUP_AGENT_UPLOAD_SESSION_TTL', 30),
        'max_bytes' => (int) env('BACKUP_AGENT_UPLOAD_MAX_BYTES', 512 * 1024 * 1024),
        /** Keep newest N successful agent uploads per hosted project; delete older. */
        'retain' => (int) env('BACKUP_AGENT_UPLOAD_RETAIN', 5),
        'path_prefix' => 'backups/agent',
    ],

];
