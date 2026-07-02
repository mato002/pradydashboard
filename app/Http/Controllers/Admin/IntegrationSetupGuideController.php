<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Integrations\IntegrationSetupGuidePresenter;
use App\Support\Rbac\Rbac;
use Illuminate\View\View;

class IntegrationSetupGuideController extends Controller
{
    public function __construct(
        private readonly IntegrationSetupGuidePresenter $presenter,
    ) {}

    public function index(): View
    {
        $guide = $this->presenter->present();

        $adminLinks = collect($guide['admin_links'])
            ->filter(fn (array $link) => Rbac::can($link['permission']))
            ->map(fn (array $link) => [
                'label' => $link['label'],
                'url' => route($link['route']),
            ])
            ->values()
            ->all();

        return view('admin.integration-setup-guide.index', [
            'guide' => $guide,
            'adminLinks' => $adminLinks,
            'initialSection' => request('section', 'overview'),
        ]);
    }
}
