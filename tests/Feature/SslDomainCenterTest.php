<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SslDomainCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_ssl_domain_center(): void
    {
        $this->get(route('ssl-domains.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_ssl_domain_center(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('ssl-domains.index'))
            ->assertOk()
            ->assertSee(__('Domain & Certificate Management Center'))
            ->assertSee(__('Managed Domains'))
            ->assertSee(__('DNS Records'));
    }

    public function test_authenticated_user_can_add_domain(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('ssl-domains.create'))
            ->assertOk()
            ->assertSee(__('Register domain'));

        $this->actingAs($user)
            ->post(route('ssl-domains.store'), [
                'domain' => 'newclient.example.com',
                'registrar' => 'Cloudflare',
                'probe_ssl' => '0',
                'auto_renew' => '1',
            ])
            ->assertRedirect(route('ssl-domains.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('managed_domains', [
            'domain' => 'newclient.example.com',
            'registrar' => 'Cloudflare',
        ]);
    }
}
