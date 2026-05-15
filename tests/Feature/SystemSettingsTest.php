<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_system_settings(): void
    {
        $this->get(route('system-settings.edit'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_system_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('system-settings.edit'))
            ->assertOk()
            ->assertSee(__('Platform Configuration Center'))
            ->assertSee(__('General'));
    }

    public function test_user_can_upload_logo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('logo.png', 200, 200);

        $this->actingAs($user)
            ->put(route('system-settings.update'), [
                'section' => 'branding',
                'logo' => $file,
            ])
            ->assertRedirect(route('system-settings.edit', ['section' => 'branding']))
            ->assertSessionHas('status');

        $path = Setting::logoPath();
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
        $this->assertNotNull(Setting::logoUrl());
    }

    public function test_user_can_set_logo_url(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('system-settings.update'), [
                'section' => 'branding',
                'logo_url' => 'https://example.com/logo.png',
            ])
            ->assertRedirect(route('system-settings.edit', ['section' => 'branding']));

        $this->assertSame('https://example.com/logo.png', Setting::logoPath());
        $this->assertSame('https://example.com/logo.png', Setting::logoUrl());
    }

    public function test_user_can_remove_logo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $path = 'branding/test.png';
        Storage::disk('public')->put($path, 'fake');
        Setting::set(Setting::LOGO_KEY, $path);

        $this->actingAs($user)
            ->put(route('system-settings.update'), [
                'section' => 'branding',
                'remove_logo' => '1',
            ])
            ->assertRedirect(route('system-settings.edit', ['section' => 'branding']));

        $this->assertNull(Setting::logoPath());
        Storage::disk('public')->assertMissing($path);
    }

    public function test_user_can_save_general_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('system-settings.update'), [
                'section' => 'general',
                'platform_name' => 'Acme Cloud',
                'company_name' => 'Acme Inc',
                'timezone' => 'UTC',
                'currency' => 'USD',
                'language' => 'en',
                'region' => 'US',
            ])
            ->assertRedirect(route('system-settings.edit', ['section' => 'general']));

        $this->assertSame('Acme Cloud', Setting::getJson('platform.general')['platform_name']);
    }

    public function test_user_can_export_configuration(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('system-settings.export'));

        $response->assertOk();
        $this->assertStringContainsString('platform_name', $response->streamedContent());
    }
}
