<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Activity\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProjectVersion;
use App\Support\ActivityLogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectVersionController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'max:50'],
            'release_date' => ['nullable', 'date'],
            'release_type' => ['required', 'in:major,minor,patch,hotfix,security'],
            'minimum_supported_version' => ['nullable', 'string', 'max:50'],
            'changelog' => ['nullable', 'string'],
            'is_current' => ['boolean'],
        ]);

        if ($request->boolean('is_current')) {
            $product->versions()->update(['is_current' => false]);
            $product->update([
                'min_supported_version' => $data['minimum_supported_version'] ?? $product->min_supported_version,
                'latest_release_date' => $data['release_date'] ?? now(),
            ]);
        }

        $version = $product->versions()->create([
            ...$data,
            'is_current' => $request->boolean('is_current'),
        ]);

        $this->activityLogger->log(
            'project.version_created',
            ActivityLogCategory::PROJECT,
            __('Version :version registered for :product', ['version' => $version->version, 'product' => $product->name]),
            $version,
            null,
            $version->only(['version', 'release_type', 'is_current']),
        );

        return back()->with('status', __('Version registered.'));
    }

    public function destroy(Product $product, ProjectVersion $version): RedirectResponse
    {
        abort_unless((int) $version->product_id === (int) $product->id, 404);

        $label = $version->version;
        $version->delete();

        $this->activityLogger->log(
            'project.version_deleted',
            ActivityLogCategory::PROJECT,
            __('Version :version removed from :product', ['version' => $label, 'product' => $product->name]),
            $product,
            ['version' => $label],
            null,
        );

        return back()->with('status', __('Version removed.'));
    }
}
