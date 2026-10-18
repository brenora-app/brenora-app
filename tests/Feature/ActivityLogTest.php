<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_and_filter_workspace_activity(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $account = Account::create([
            'name' => 'Northstar Labs',
            'industry' => 'Software',
            'owner' => 'Workspace owner',
            'annual_revenue' => 100000,
            'health_score' => 80,
            'status' => 'healthy',
            'renewal_date' => '2027-05-01',
        ]);
        $account->activity()->create([
            'user_id' => $admin->id,
            'event_type' => 'forecast_changed',
            'summary' => 'Forecast updated after renewal review.',
            'details' => ['from' => 100000, 'to' => 125000],
        ]);

        $this->actingAs($admin)->get(route('activity.index', ['q' => 'Northstar']))
            ->assertOk()
            ->assertSee('Forecast updated after renewal review.')
            ->assertSee($admin->name)
            ->assertSee('View recorded details');

        $this->get(route('activity.index', ['event' => 'forecast_changed']))
            ->assertOk()
            ->assertSee($account->name);
    }

    public function test_non_admin_cannot_open_workspace_activity_log(): void
    {
        $analyst = User::factory()->create();
        $analyst->forceFill(['role' => 'analyst'])->save();

        $this->actingAs($analyst)->get(route('activity.index'))->assertForbidden();
    }
}
