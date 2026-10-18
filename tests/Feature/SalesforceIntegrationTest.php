<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\SalesforceConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SalesforceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_open_salesforce_settings(): void
    {
        $viewer = User::factory()->create();
        $viewer->forceFill(['role' => 'viewer'])->save();

        $this->actingAs($viewer)->get(route('salesforce.index'))->assertForbidden();
    }

    public function test_admin_can_open_unconfigured_salesforce_settings(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        config([
            'services.salesforce.client_id' => null,
            'services.salesforce.client_secret' => null,
        ]);

        $this->actingAs($admin)->get(route('salesforce.index'))
            ->assertOk()
            ->assertSee('Connect credentials are not configured')
            ->assertSee('Connect Salesforce')
            ->assertSee('disabled', false);
    }

    public function test_admin_can_complete_salesforce_oauth_and_tokens_are_stored_encrypted(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        config([
            'services.salesforce.client_id' => 'client-id',
            'services.salesforce.client_secret' => 'client-secret',
            'services.salesforce.redirect_uri' => 'https://brenora.net/admin/integrations/salesforce/callback',
            'services.salesforce.login_url' => 'https://login.salesforce.com',
        ]);

        Http::fake([
            'https://login.salesforce.com/services/oauth2/token' => Http::response([
                'access_token' => 'access-token',
                'refresh_token' => 'refresh-token',
                'instance_url' => 'https://acme.my.salesforce.com',
            ]),
        ]);

        $this->actingAs($admin)->get(route('salesforce.connect'))->assertRedirect();
        $state = session('salesforce_oauth_state');

        $this->get(route('salesforce.callback', ['state' => $state, 'code' => 'authorization-code']))
            ->assertRedirect(route('salesforce.index'))
            ->assertSessionHas('status');

        $connection = SalesforceConnection::query()->firstOrFail();
        $this->assertSame('access-token', $connection->access_token);
        $this->assertSame('refresh-token', $connection->refresh_token);
        $this->assertSame($admin->id, $connection->connected_by);
        $this->assertDatabaseMissing('salesforce_connections', [
            'id' => $connection->id,
            'access_token' => 'access-token',
        ]);
    }

    public function test_manual_salesforce_sync_preserves_unknown_source_values_for_review(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $connection = SalesforceConnection::create([
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'instance_url' => 'https://acme.my.salesforce.com',
            'connected_by' => $admin->id,
        ]);
        $accountPayload = ['records' => [[
            'Id' => '001ABCDEF123456',
            'Name' => 'Northstar Labs',
            'Industry' => 'Software',
            'AnnualRevenue' => null,
            'Owner' => ['Name' => 'Salesforce Owner'],
        ]]];
        $contactPayload = ['records' => [[
            'Id' => '003ABCDEF123456',
            'AccountId' => '001ABCDEF123456',
            'Name' => 'Jordan Lee',
            'Title' => 'Operations lead',
            'Email' => 'jordan@example.test',
            'Phone' => null,
        ]]];
        Http::fake([
            'https://acme.my.salesforce.com/services/data/v60.0/query*' => Http::sequence()
                ->push($accountPayload)
                ->push($contactPayload)
                ->push($accountPayload)
                ->push($contactPayload),
        ]);

        $this->actingAs($admin)->post(route('salesforce.sync'))
            ->assertRedirect()
            ->assertSessionHas('status');

        $account = Account::query()->where('salesforce_id', '001ABCDEF123456')->firstOrFail();
        $this->assertSame('needs_review', $account->status);
        $this->assertSame('unknown', $account->engagement_status);
        $this->assertNull($account->annual_revenue);
        $this->assertNull($account->health_score);
        $this->assertNull($account->renewal_date);
        $this->assertNull($account->opportunity_stage);
        $this->assertSame('pending', $account->consent_status);
        $this->assertDatabaseHas('account_contacts', [
            'account_id' => $account->id,
            'salesforce_id' => '003ABCDEF123456',
            'consent_status' => 'pending',
            'consent_updated_at' => null,
        ]);
        $this->assertNotNull($connection->fresh()->last_synced_at);

        $contact = $account->contacts()->firstOrFail();
        $account->update([
            'status' => 'healthy',
            'health_score' => 88,
            'consent_status' => 'granted',
            'segment' => 'enterprise',
            'opportunity_stage' => 'proposal',
        ]);
        $contact->update(['consent_status' => 'granted', 'is_primary' => true]);

        $this->actingAs($admin)->post(route('salesforce.sync'))->assertRedirect();

        $this->assertSame('healthy', $account->fresh()->status);
        $this->assertSame(88, $account->fresh()->health_score);
        $this->assertSame('granted', $account->fresh()->consent_status);
        $this->assertSame('enterprise', $account->fresh()->segment);
        $this->assertSame('proposal', $account->fresh()->opportunity_stage);
        $this->assertSame('granted', $contact->fresh()->consent_status);
        $this->assertTrue($contact->fresh()->is_primary);
    }

    public function test_salesforce_oauth_callback_rejects_a_mismatched_state(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)->withSession(['salesforce_oauth_state' => 'expected-state'])
            ->get(route('salesforce.callback', ['state' => 'attacker-state', 'code' => 'authorization-code']))
            ->assertForbidden();

        $this->assertDatabaseCount('salesforce_connections', 0);
    }

    public function test_disconnect_removes_credentials_but_retains_imported_records(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        SalesforceConnection::create([
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'instance_url' => 'https://acme.my.salesforce.com',
        ]);
        $account = Account::create([
            'name' => 'Northstar Labs',
            'salesforce_id' => '001ABCDEF123456',
            'industry' => null,
            'owner' => null,
            'annual_revenue' => null,
            'health_score' => null,
            'status' => 'needs_review',
            'renewal_date' => null,
            'engagement_status' => 'unknown',
            'segment' => 'unclassified',
            'expansion_value' => null,
            'opportunity_stage' => null,
            'consent_status' => 'pending',
        ]);

        $this->actingAs($admin)->delete(route('salesforce.disconnect'))->assertRedirect();

        $this->assertDatabaseCount('salesforce_connections', 0);
        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'salesforce_id' => '001ABCDEF123456']);
    }
}
