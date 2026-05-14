<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Server;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->with('server')
            ->withCount('tenants')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.projects.index', compact('projects'));
    }

    public function create(): View
    {
        $servers = Server::query()->orderBy('name')->get();

        return view('admin.projects.create', [
            'project' => new Project,
            'servers' => $servers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Project::query()->create($data);

        return redirect()->route('projects.index')->with('status', 'Hosted project created.');
    }

    public function show(Project $project): View
    {
        $project->load(['server', 'tenants']);

        return view('admin.projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        $servers = Server::query()->orderBy('name')->get();

        return view('admin.projects.edit', compact('project', 'servers'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $this->validated($request);
        $project->update($data);

        return redirect()->route('projects.show', $project)->with('status', 'Hosted project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')->with('status', 'Hosted project removed.');
    }

    public function regenerateToken(Project $project): RedirectResponse
    {
        $project->update(['api_token' => Str::random(64)]);

        return back()->with('status', 'API token regenerated. Update all tenant systems with the new token.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $request->merge([
            'product_slug' => filled(trim((string) $request->input('product_slug', '')))
                ? strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace('-', '_', trim((string) $request->input('product_slug')))))
                : null,
        ]);

        return $request->validate([
            'server_id' => ['nullable', 'exists:servers,id'],
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'product_slug' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('projects', 'product_slug')->ignore($request->route('project')?->getKey()),
            ],
            'technology_stack' => ['nullable', 'string'],
            'git_repository' => ['nullable', 'string', 'max:500'],
            'database_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,maintenance,suspended'],
            'version' => ['nullable', 'string', 'max:100'],
            'monthly_revenue' => ['nullable', 'numeric', 'min:0'],
            'monthly_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
