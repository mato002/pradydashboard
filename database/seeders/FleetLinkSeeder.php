<?php

namespace Database\Seeders;

use App\Models\HostedProject;
use App\Models\Server;
use Illuminate\Database\Seeder;

class FleetLinkSeeder extends Seeder
{
    public function run(): void
    {
        $servers = Server::query()->orderBy('name')->get();

        if ($servers->isEmpty()) {
            return;
        }

        foreach (HostedProject::query()->orderBy('id')->get() as $index => $hostedProject) {
            if ($hostedProject->server_id !== null) {
                continue;
            }

            $hostedProject->update([
                'server_id' => $servers[$index % $servers->count()]->id,
            ]);
        }
    }
}
