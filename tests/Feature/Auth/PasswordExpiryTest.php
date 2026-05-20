<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_password_redirects_to_forced_change_screen(): void
    {
        $user = User::factory()->create([
            'password_changed_at' => now()->subDays(29),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('password.expired'));
    }

    public function test_fresh_password_skips_forced_change_screen(): void
    {
        $user = User::factory()->create([
            'password_changed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('password.expired'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_user_can_update_expired_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'password_changed_at' => now()->subDays(30),
        ]);

        $response = $this->actingAs($user)->put(route('password.expired.update'), [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertFalse($user->fresh()->passwordExpired());
    }
}
