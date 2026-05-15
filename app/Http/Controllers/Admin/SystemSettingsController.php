<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Settings\PlatformSettingsService;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemSettingsController extends Controller
{
    public function __construct(
        private readonly PlatformSettingsService $settings
    ) {}

    public function edit(Request $request): View
    {
        $section = $this->settings->section(
            (string) ($request->query('section') ?? session('section', 'general'))
        );
        $all = $this->settings->all();

        return view('admin.system-settings.edit', [
            'activeSection' => $section,
            'navSections' => PlatformSettingsService::SECTIONS,
            'platformConfig' => $all,
            'sectionData' => $all[$section] ?? [],
            'health' => $this->settings->healthWidgets(),
            'delivery' => $this->settings->deliveryStats(),
            'logoUrl' => Setting::logoUrl(),
            'logoPath' => Setting::logoPath(),
            'logoDarkUrl' => Setting::logoDarkUrl(),
            'faviconUrl' => Setting::faviconUrl(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $section = $this->settings->section((string) $request->input('section', 'general'));

        if ($section === 'branding') {
            return $this->updateBranding($request);
        }

        if (in_array($section, ['logs', 'audit'], true)) {
            return $this->redirectWithStatus(__('This section is read-only.'), $section);
        }

        $this->settings->saveSection($section, $request->except(['_token', '_method', 'section']));

        return $this->redirectWithStatus(__('Settings saved.'), $section);
    }

    public function export(): StreamedResponse
    {
        $payload = $this->settings->exportConfiguration();
        $filename = 'platform-config-'.now()->format('Y-m-d-His').'.json';

        return response()->streamDownload(
            fn () => print (json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)),
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    public function restoreDefaults(Request $request): RedirectResponse
    {
        $section = $this->settings->section((string) $request->input('section', 'general'));

        if ($section === 'branding') {
            Setting::deleteStoredLogo();
            Setting::set(Setting::LOGO_KEY, null);
            Setting::deleteStoredFile(Setting::get(Setting::LOGO_DARK_KEY));
            Setting::set(Setting::LOGO_DARK_KEY, null);
            Setting::deleteStoredFile(Setting::get(Setting::FAVICON_KEY));
            Setting::set(Setting::FAVICON_KEY, null);
        }

        Setting::setJson('platform.'.$section, $this->settings->defaults($section));

        return $this->redirectWithStatus(__('Defaults restored.'), $section);
    }

    private function updateBranding(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:2048'],
            'logo_dark' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,ico', 'max:1024'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'remove_logo' => ['sometimes', 'boolean'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'font_family' => ['nullable', 'string', 'max:80'],
            'login_tagline' => ['nullable', 'string', 'max:255'],
            'sidebar_style' => ['nullable', 'string', 'max:40'],
        ]);

        if ($request->boolean('remove_logo')) {
            Setting::deleteStoredLogo();
            Setting::set(Setting::LOGO_KEY, null);

            return $this->redirectWithStatus(__('Logo removed.'), 'branding');
        }

        $this->storeBrandingFile($request, 'logo', Setting::LOGO_KEY, true);
        $this->storeBrandingFile($request, 'logo_dark', Setting::LOGO_DARK_KEY, false);
        $this->storeBrandingFile($request, 'favicon', Setting::FAVICON_KEY, false);

        if (! empty($validated['logo_url'])) {
            Setting::deleteStoredLogo();
            Setting::set(Setting::LOGO_KEY, $validated['logo_url']);
        }

        $this->settings->saveSection('branding', $request->only([
            'primary_color', 'accent_color', 'font_family', 'login_tagline', 'sidebar_style',
        ]));

        return $this->redirectWithStatus(__('Branding updated.'), 'branding');
    }

    private function storeBrandingFile(Request $request, string $input, string $key, bool $replaceLogo): void
    {
        if (! $request->hasFile($input)) {
            return;
        }

        if ($replaceLogo) {
            Setting::deleteStoredLogo();
        } else {
            Setting::deleteStoredFile(Setting::get($key));
        }

        $path = $request->file($input)->store('branding', 'public');
        Setting::set($key, $path);
    }

    private function redirectWithStatus(string $message, ?string $section = null): RedirectResponse
    {
        $params = $section ? ['section' => $section] : [];

        return redirect()
            ->route('system-settings.edit', $params)
            ->with('status', $message);
    }
}
