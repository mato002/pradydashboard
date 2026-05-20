<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogCategory;
use App\Models\Server;
use App\Models\ServerProviderNotice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServerProviderNoticeController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function store(Request $request, Server $server): RedirectResponse
    {
        $data = $this->validated($request);
        $data['server_id'] = $server->id;

        ServerProviderNotice::query()->create($data);

        return redirect()
            ->route('servers.show', $server)
            ->withFragment('notices')
            ->with('status', __('Provider notice added.'));
    }

    public function update(Request $request, Server $server, ServerProviderNotice $notice): RedirectResponse
    {
        abort_unless($notice->server_id === $server->id, 404);

        $data = $this->validated($request);
        $old = $notice->only(array_keys($data));
        $notice->update($data);

        $action = ($data['status'] ?? null) === 'resolved' && ($old['status'] ?? null) !== 'resolved'
            ? 'server.notice_resolved'
            : 'server.notice_updated';

        $description = $action === 'server.notice_resolved'
            ? __('Provider notice resolved on :server: :title', ['server' => $server->name, 'title' => $notice->title])
            : __('Provider notice updated on :server: :title', ['server' => $server->name, 'title' => $notice->title]);

        $this->activityLogger->log(
            $action,
            ActivityLogCategory::SERVER,
            $description,
            $notice,
            $old,
            $notice->only(array_keys($data)),
        );

        return redirect()
            ->route('servers.show', $server)
            ->withFragment('notices')
            ->with('status', __('Provider notice updated.'));
    }

    public function destroy(Server $server, ServerProviderNotice $notice): RedirectResponse
    {
        abort_unless($notice->server_id === $server->id, 404);

        $notice->delete();

        return redirect()
            ->route('servers.show', $server)
            ->withFragment('notices')
            ->with('status', __('Provider notice removed.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'source' => ['nullable', 'string', 'max:255'],
            'notice_type' => ['required', 'string', 'in:'.implode(',', ServerProviderNotice::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'severity' => ['required', 'string', 'in:'.implode(',', ServerProviderNotice::SEVERITIES)],
            'notice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:'.implode(',', ServerProviderNotice::STATUSES)],
            'source_reference' => ['nullable', 'string', 'max:255'],
            'attachment_reference' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
