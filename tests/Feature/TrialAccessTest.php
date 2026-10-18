<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrialAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_trial_is_redirected_from_the_workspace(): void
    {
        $user = User::factory()->create([
            'trial_ends_at' => now()->subMinute(),
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertRedirect(route('home').'#pricing')
            ->assertSessionHas('trial_expired', true);
    }

    public function test_expired_trial_keeps_profile_management_available(): void
    {
        $user = User::factory()->create([
            'trial_ends_at' => now()->subMinute(),
        ]);

        $this->actingAs($user)->get(route('profile.edit'))->assertOk();
    }

    public function test_legacy_user_without_trial_expiry_keeps_workspace_access(): void
    {
        $user = User::factory()->create([
            'trial_ends_at' => null,
        ]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }
}
