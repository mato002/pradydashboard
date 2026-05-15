<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_subscription_center(): void
    {
        $this->get(route('subscriptions.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_subscription_center(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('subscriptions.index'))
            ->assertOk()
            ->assertSee(__('Subscription & Billing Center'))
            ->assertSee(__('MRR'))
            ->assertSee(__('Starter'));
    }
}
