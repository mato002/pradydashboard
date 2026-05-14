<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Server;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServerController extends Controller
{
    public function index(): View
    {
        $servers = Server::query()
            ->withCount(['projects', 'tenants'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.servers.index', compact('servers'));
    }

    public function create(): View
    {
        return view('admin.servers.create', ['server' => new Server]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['hosted_domains'] = $this->parseDomains($request->string('hosted_domains_text')->toString());

        Server::query()->create($data);

        return redirect()->route('servers.index')->with('status', 'Server created.');
    }

    public function show(Server $server): View
    {
        $server->load(['projects', 'tenants']);

        return view('admin.servers.show', compact('server'));
    }

    public function edit(Server $server): View
    {
        return view('admin.servers.edit', compact('server'));
    }

    public function update(Request $request, Server $server): RedirectResponse
    {
        $data = $this->validated($request);
        $data['hosted_domains'] = $this->parseDomains($request->string('hosted_domains_text')->toString());

        $server->update($data);

        return redirect()->route('servers.show', $server)->with('status', 'Server updated.');
    }

    public function destroy(Server $server): RedirectResponse
    {
        $server->delete();

        return redirect()->route('servers.index')->with('status', 'Server removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'whm_cpanel_reference' => ['nullable', 'string'],
            'cpu_cores' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'ram_gb' => ['nullable', 'numeric', 'min:0'],
            'storage_gb' => ['nullable', 'numeric', 'min:0'],
            'disk_usage_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:online,offline,unknown'],
            'ssl_status' => ['nullable', 'string', 'max:255'],
            'backup_status' => ['nullable', 'string', 'max:255'],
            'renewal_expires_at' => ['nullable', 'date'],
            'monthly_cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'monthly_revenue' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    /**
     * @return array<int, string>|null
     */
    private function parseDomains(string $text): ?array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $domains = array_values(array_filter(array_map('trim', $lines)));

        return $domains === [] ? null : $domains;
    }
}
