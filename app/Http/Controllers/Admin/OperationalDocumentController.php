<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Concerns\AuthorizesRbacScope;
use App\Http\Controllers\Controller;
use App\Models\OperationalDocument;
use App\Support\ActivityLogCategory;
use App\Models\Tenant;
use App\Support\OperationalDocumentOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperationalDocumentController extends Controller
{
    use AuthorizesRbacScope;

    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorizeTenantRbac($tenant, 'update');

        $data = $this->validated($request, $tenant, requireFile: true);
        $path = $request->file('file')->store("operational-documents/{$tenant->id}", 'local');

        $document = OperationalDocument::query()->create([
            ...$data,
            'tenant_id' => $tenant->id,
            'file_path' => $path,
            'uploaded_by' => $request->user()?->id,
        ]);

        $this->activityLogger->log(
            'document.uploaded',
            ActivityLogCategory::DOCUMENT,
            __('Document uploaded: :title', ['title' => $document->title]),
            $document,
            null,
            ['document_type' => $document->document_type, 'file_path' => $path],
        );

        return $this->redirectBack($tenant, $data['tenant_project_subscription_id'] ?? null)
            ->with('status', __('Document uploaded.'));
    }

    public function update(Request $request, Tenant $tenant, OperationalDocument $document): RedirectResponse
    {
        $this->authorizeTenantRbac($tenant, 'update');
        abort_unless($document->tenant_id === $tenant->id, 404);

        $data = $this->validated($request, $tenant, requireFile: false);

        if ($request->hasFile('file')) {
            if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }
            $data['file_path'] = $request->file('file')->store("operational-documents/{$tenant->id}", 'local');
        }

        $old = $document->only(array_merge(array_keys($data), ['file_path']));
        $document->update($data);

        $this->activityLogger->log(
            'document.updated',
            ActivityLogCategory::DOCUMENT,
            __('Document updated: :title', ['title' => $document->title]),
            $document,
            $old,
            $document->only(array_merge(array_keys($data), ['file_path'])),
        );

        return $this->redirectBack($tenant, $document->tenant_project_subscription_id)
            ->with('status', __('Document updated.'));
    }

    public function destroy(Tenant $tenant, OperationalDocument $document): RedirectResponse
    {
        $this->authorizeTenantRbac($tenant, 'update');
        abort_unless($document->tenant_id === $tenant->id, 404);

        if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $subscriptionId = $document->tenant_project_subscription_id;
        $title = $document->title;
        $document->delete();

        $this->activityLogger->log(
            'document.deleted',
            ActivityLogCategory::DOCUMENT,
            __('Document deleted: :title', ['title' => $title]),
            $tenant,
            ['title' => $title],
            null,
        );

        return $this->redirectBack($tenant, $subscriptionId)
            ->with('status', __('Document removed.'));
    }

    public function download(Tenant $tenant, OperationalDocument $document): StreamedResponse
    {
        $this->authorizeTenantRbac($tenant, 'view');
        abort_unless($document->tenant_id === $tenant->id, 404);
        abort_unless(
            $document->file_path && Storage::disk('local')->exists($document->file_path),
            404,
            __('File not found.')
        );

        $this->activityLogger->log(
            'document.downloaded',
            ActivityLogCategory::DOCUMENT,
            __('Document downloaded: :title', ['title' => $document->title]),
            $document,
            null,
            ['file_path' => $document->file_path],
        );

        return Storage::disk('local')->download(
            $document->file_path,
            basename($document->file_path)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, Tenant $tenant, bool $requireFile): array
    {
        $subscriptionIds = $tenant->projectSubscriptions()->pluck('id');

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', Rule::in(array_keys(OperationalDocumentOptions::documentTypes()))],
            'tenant_project_subscription_id' => [
                'nullable',
                Rule::in($subscriptionIds->all()),
            ],
            'product_id' => ['nullable', 'exists:products,id'],
            'status' => ['required', Rule::in(array_keys(OperationalDocumentOptions::statuses()))],
            'signed_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:signed_date'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'file' => [$requireFile ? 'required' : 'nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,txt,zip'],
        ];

        $data = $request->validate($rules);

        if (empty($data['product_id']) && ! empty($data['tenant_project_subscription_id'])) {
            $sub = $tenant->projectSubscriptions->firstWhere('id', (int) $data['tenant_project_subscription_id']);
            $data['product_id'] = $sub?->product_id;
        }

        unset($data['file']);

        return $data;
    }

    private function redirectBack(Tenant $tenant, ?int $subscriptionId): RedirectResponse
    {
        $params = ['tenant' => $tenant, 'tab' => 'documents'];
        if ($subscriptionId) {
            $params['subscription'] = $subscriptionId;
        }

        return redirect()->route('tenants.show', $params);
    }
}
