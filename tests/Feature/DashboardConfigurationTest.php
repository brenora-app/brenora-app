<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountCustomField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_managers_can_define_and_apply_custom_account_fields(): void
    {
        $analyst = User::factory()->create();
        $analyst->forceFill(['role' => 'analyst'])->save();

        $this->actingAs($analyst)->get(route('custom-fields.index'))
            ->assertOk()
            ->assertSee('Custom account fields');

        $this->post(route('custom-fields.store'), [
            'name' => 'Customer tier',
            'type' => 'select',
            'options_text' => "Strategic\nGrowth\nStandard",
            'required' => '1',
        ])->assertSessionHasNoErrors();

        $field = AccountCustomField::query()->sole();
        $this->assertSame('customer_tier', $field->key);
        $this->assertTrue($field->required);

        $this->post(route('accounts.store'), [
            ...$this->accountData(),
            'custom_fields' => [$field->key => 'Growth'],
        ])->assertRedirect();

        $account = Account::query()->sole();
        $this->assertSame('Growth', $account->custom_fields[$field->key]);

        $this->actingAs($analyst)->get(route('accounts.show', $account))
            ->assertOk()
            ->assertSee('Customer tier')
            ->assertSee('Growth');
    }

    public function test_required_custom_fields_are_validated_when_saving_accounts(): void
    {
        $analyst = User::factory()->create();
        $analyst->forceFill(['role' => 'analyst'])->save();
        AccountCustomField::create([
            'name' => 'Renewal stage',
            'key' => 'renewal_stage',
            'type' => 'select',
            'options' => ['Planning', 'Negotiating'],
            'required' => true,
        ]);

        $this->actingAs($analyst)->post(route('accounts.store'), $this->accountData())
            ->assertSessionHasErrors('custom_fields.renewal_stage');

        $this->assertDatabaseCount('accounts', 0);
    }

    public function test_viewers_cannot_manage_custom_field_definitions(): void
    {
        $viewer = User::factory()->create();

        $this->actingAs($viewer)->get(route('custom-fields.index'))->assertForbidden();
        $this->actingAs($viewer)->post(route('custom-fields.store'), [
            'name' => 'Region',
            'type' => 'text',
        ])->assertForbidden();
    }

    public function test_account_status_filter_limits_the_account_list(): void
    {
        $viewer = User::factory()->create();
        $viewer->forceFill(['role' => 'viewer'])->save();
        $atRisk = Account::create([...$this->accountData(), 'name' => 'Needs attention', 'status' => 'at_risk']);
        Account::create([...$this->accountData(), 'name' => 'Healthy customer', 'status' => 'healthy']);

        $this->actingAs($viewer)->get(route('accounts.index', ['status' => 'at_risk']))
            ->assertOk()
            ->assertSee($atRisk->name)
            ->assertDontSee('Healthy customer');
    }

    public function test_removing_a_custom_field_removes_its_saved_account_values(): void
    {
        $analyst = User::factory()->create();
        $analyst->forceFill(['role' => 'analyst'])->save();
        $field = AccountCustomField::create([
            'name' => 'Region',
            'key' => 'region',
            'type' => 'text',
            'required' => false,
        ]);
        $account = Account::create([
            ...$this->accountData(),
            'custom_fields' => ['region' => 'North'],
        ]);

        $this->actingAs($analyst)->delete(route('custom-fields.destroy', $field))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('account_custom_fields', ['id' => $field->id]);
        $this->assertArrayNotHasKey('region', $account->fresh()->custom_fields);
    }

    public function test_dashboard_preferences_are_saved_per_user_and_control_visible_sections(): void
    {
        $viewer = User::factory()->create();
        $account = Account::create($this->accountData());

        $this->actingAs($viewer)->patch(route('settings.dashboard.update'), [
            'sections' => [
                'metrics' => '0',
                'accounts' => '1',
                'insights' => '0',
                'actions' => '0',
            ],
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame([
            'metrics' => false,
            'accounts' => true,
            'insights' => false,
            'actions' => false,
        ], $viewer->fresh()->dashboard_preferences);

        $this->actingAs($viewer)->get(route('dashboard'))
            ->assertOk()
            ->assertSee($account->name)
            ->assertDontSee('Portfolio value shown')
            ->assertDontSee('Latest insights')
            ->assertDontSee('Commercial actions');
    }

    private function accountData(): array
    {
        return [
            'name' => 'Northstar Labs',
            'industry' => 'Software',
            'owner' => 'Nia Chen',
            'annual_revenue' => 125000,
            'health_score' => 82,
            'status' => 'healthy',
            'renewal_date' => '2027-05-20',
        ];
    }
}
