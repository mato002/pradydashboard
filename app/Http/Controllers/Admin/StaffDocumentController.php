<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Support\ActivityLogCategory;
use App\Models\StaffDocument;
use App\Models\StaffProfile;
use App\Support\StaffDocumentOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffDocumentController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function store(Request $request, StaffProfile $staff): RedirectResponse
    {
        $data = $this->validated($request, requireFile: true);
        $path = $request->file('file')->store("staff-documents/{$staff->id}", 'local');

        $staff->documents()->create([
            ...$data,
            'file_path' => $path,
            'uploaded_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('hr.staff.show', ['staff' => $staff, 'tab' => 'documents'])
            ->with('status', __('Document uploaded.'));
    }

    public function destroy(StaffProfile $staff, StaffDocument $document): RedirectResponse
    {
        abort_unless($document->staff_profile_id === $staff->id, 404);

        if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $title = $document->title;
        $document->delete();

        $this->activityLogger->log(
            'staff.document_deleted',
            ActivityLogCategory::HR,
            __('Document deleted for :name: :title', ['name' => $staff->full_name, 'title' => $title]),
            $staff,
            ['title' => $title],
            null,
        );

        return redirect()
            ->route('hr.staff.show', ['staff' => $staff, 'tab' => 'documents'])
            ->with('status', __('Document removed.'));
    }

    public function download(StaffProfile $staff, StaffDocument $document): StreamedResponse
    {
        abort_unless($document->staff_profile_id === $staff->id, 404);
        abort_unless(
            $document->file_path && Storage::disk('local')->exists($document->file_path),
            404,
            __('File not found.')
        );

        $this->activityLogger->log(
            'staff.document_downloaded',
            ActivityLogCategory::HR,
            __('Document downloaded for :name: :title', ['name' => $staff->full_name, 'title' => $document->title]),
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
    private function validated(Request $request, bool $requireFile): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', Rule::in(array_keys(StaffDocumentOptions::documentTypes()))],
            'signed_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:signed_date'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'file' => [$requireFile ? 'required' : 'nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,png,jpg,jpeg,txt,zip'],
        ]);

        unset($data['file']);

        return $data;
    }
}
