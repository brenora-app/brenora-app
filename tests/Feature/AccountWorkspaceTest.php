<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CommercialAction;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AccountWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_view_accounts_but_cannot_create_them(): void
    {
        $viewer = User::factory()->create();
        $viewer->forceFill(['role' => 'viewer'])->save();

        $this->actingAs($viewer)->get(route('accounts.index'))->assertOk();
        $this->actingAs($viewer)->post(route('accounts.store'), $this->accountData())->assertForbidden();
    }

    public function test_registration_cannot_assign_an_elevated_role(): void
    {
        $this->post(route('register'), [
            'name' => 'New member',
            'email' => 'member@example.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'admin',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'member@example.test',
            'role' => 'viewer',
        ]);
    }

    public function test_analyst_can_create_account_and_nested_signal(): void
    {
        $analyst = User::factory()->create();
        $analyst->forceFill(['role' => 'analyst'])->save();

        $this->actingAs($analyst)->post(route('accounts.store'), $this->accountData())
            ->assertRedirect();

        $account = Account::query()->firstOrFail();

        $this->actingAs($analyst)->post(route('accounts.signals.store', $account), [
            'type' => 'usage',
            'source' => 'Product Analytics',
            'score' => 82,
            'recorded_at' => now()->format('Y-m-d H:i:s'),
            'summary' => 'Weekly active seats increased.',
        ])->assertRedirect(route('accounts.show', $account));

        $this->assertDatabaseHas('signals', [
            'account_id' => $account->id,
            'source' => 'Product Analytics',
        ]);
    }

    public function test_dashboard_uses_persisted_records_and_renders_for_viewer(): void
    {
        $viewer = User::factory()->create();
        $viewer->forceFill(['role' => 'viewer'])->save();
        $account = Account::create($this->accountData());
        $account->signals()->create([
            'type' => 'usage',
            'source' => 'Product Analytics',
            'score' => 82,
            'recorded_at' => now(),
            'summary' => 'Weekly active seats increased.',
        ]);
        $account->insights()->create([
            'title' => 'Usage increase',
            'summary' => 'Review the observed usage signal.',
            'severity' => 'information',
            'status' => 'draft',
            'model_name' => 'sample',
            'evidence' => ['Signal #1'],
        ]);
        $account->actions()->create([
            'title' => 'Customer check-in',
            'owner' => 'Nia Chen',
            'status' => 'open',
        ]);

        $this->actingAs($viewer)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Welcome back,')
            ->assertSee('Northstar Labs')
            ->assertSee('Usage increase')
            ->assertSee('Customer check-in');
    }

    public function test_contributor_cannot_assign_an_admin_role(): void
    {
        $contributor = User::factory()->create();
        $contributor->forceFill(['role' => 'contributor'])->save();
        $member = User::factory()->create();
        $member->forceFill(['role' => 'viewer'])->save();

        $this->actingAs($contributor)->patch(route('admin.users.role', $member), ['role' => 'admin'])
            ->assertForbidden();

        $this->assertSame('viewer', $member->fresh()->role);
    }

    public function test_required_approval_prevents_direct_action_completion(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $account = Account::create($this->accountData());
        $action = CommercialAction::create([
            'account_id' => $account->id,
            'created_by' => $admin->id,
            'title' => 'Renewal approval',
            'owner' => 'Demo User',
            'status' => 'in_progress',
            'approval_required' => true,
        ]);

        $this->actingAs($admin)->patch(route('admin.actions.update', $action), [
            'status' => 'completed',
            'note' => 'Attempted completion',
        ])->assertSessionHasErrors('status');

        $this->assertSame('in_progress', $action->fresh()->status);
        $this->assertDatabaseMissing('action_events', ['commercial_action_id' => $action->id]);
    }

    public function test_action_cannot_be_completed_without_recording_an_outcome(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $account = Account::create($this->accountData());
        $action = CommercialAction::create([
            'account_id' => $account->id,
            'created_by' => $admin->id,
            'title' => 'Customer follow-up',
            'owner' => 'Workspace owner',
            'status' => 'in_progress',
        ]);

        $this->actingAs($admin)->patch(route('admin.actions.update', $action), [
            'status' => 'completed',
            'note' => 'Marked complete without a result.',
        ])->assertSessionHasErrors('outcome');

        $this->assertSame('in_progress', $action->fresh()->status);
        $this->assertDatabaseMissing('action_events', ['commercial_action_id' => $action->id]);
    }

    public function test_approved_action_requires_an_outcome_and_records_assignment_and_completion(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $assignee = User::factory()->create();
        $account = Account::create($this->accountData());
        $action = CommercialAction::create([
            'account_id' => $account->id,
            'created_by' => $admin->id,
            'title' => 'Renewal discussion',
            'owner' => 'Workspace owner',
            'status' => 'in_progress',
            'approval_required' => true,
        ]);

        $this->actingAs($admin)->patch(route('admin.actions.update', $action), [
            'status' => 'pending_approval',
            'note' => 'Sensitive customer outreach requires review.',
            'assigned_to' => $assignee->id,
            'owner' => $assignee->name,
            'due_date' => '2027-02-01',
        ])->assertSessionHasNoErrors();

        $this->patch(route('admin.actions.update', $action), [
            'status' => 'completed',
            'note' => 'Approved follow-up completed.',
            'outcome' => 'Customer confirmed the renewal review date.',
            'assigned_to' => $assignee->id,
            'owner' => $assignee->name,
            'due_date' => '2027-02-01',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('commercial_actions', [
            'id' => $action->id,
            'assigned_to' => $assignee->id,
            'status' => 'completed',
            'outcome' => 'Customer confirmed the renewal review date.',
        ]);
        $this->assertNotNull($action->fresh()->completed_at);
        $this->assertDatabaseHas('action_events', [
            'commercial_action_id' => $action->id,
            'from_status' => 'pending_approval',
            'to_status' => 'completed',
            'user_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('account_activity', [
            'account_id' => $account->id,
            'event_type' => 'action_status_changed',
        ]);
    }

    public function test_admin_can_update_member_role_and_action_history_is_persisted(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $member = User::factory()->create();
        $member->forceFill(['role' => 'viewer'])->save();
        $account = Account::create($this->accountData());

        $this->actingAs($admin)->patch(route('admin.users.role', $member), ['role' => 'analyst'])
            ->assertSessionHasNoErrors();
        $this->assertSame('analyst', $member->fresh()->role);

        $this->post(route('admin.actions.store'), [
            'account_id' => $account->id,
            'title' => 'Schedule customer check-in',
            'owner' => 'Nia Chen',
            'due_date' => '2027-01-10',
            'description' => 'Confirm customer priorities.',
        ])->assertSessionHasNoErrors();

        $action = CommercialAction::query()->firstOrFail();
        $this->patch(route('admin.actions.update', $action), [
            'status' => 'in_progress',
            'note' => 'Owner has started the follow-up.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('action_events', [
            'commercial_action_id' => $action->id,
            'from_status' => 'open',
            'to_status' => 'in_progress',
            'user_id' => $admin->id,
        ]);
    }

    public function test_analyst_review_is_attributed_to_the_user(): void
    {
        $analyst = User::factory()->create();
        $analyst->forceFill(['role' => 'analyst'])->save();
        $account = Account::create($this->accountData());
        $insight = Insight::create([
            'account_id' => $account->id,
            'title' => 'Review account activity',
            'summary' => 'Confirm activity with the customer owner.',
            'status' => 'draft',
            'evidence' => ['Signal #1'],
        ]);

        $this->actingAs($analyst)->post(route('insights.review', $insight), [
            'status' => 'reviewed',
            'reviewer_correction' => 'The usage statement is supported, but confirm the renewal date with the account owner.',
        ])
            ->assertSessionHasNoErrors();

        $this->assertSame('reviewed', $insight->fresh()->status);
        $this->assertSame($analyst->id, $insight->fresh()->reviewed_by);
        $this->assertNotNull($insight->fresh()->reviewed_at);
        $this->assertSame('The usage statement is supported, but confirm the renewal date with the account owner.', $insight->fresh()->reviewer_correction);
    }

    public function test_analyst_can_generate_evidence_linked_insight_using_ollama(): void
    {
        $analyst = User::factory()->create();
        $analyst->forceFill(['role' => 'analyst'])->save();
        $account = Account::create($this->accountData() + ['consent_status' => 'granted']);
        $signal = $account->signals()->create([
            'type' => 'usage',
            'source' => 'Product Analytics',
            'score' => 82,
            'recorded_at' => now(),
            'summary' => 'Weekly active seats increased.',
        ]);
        config(['services.ollama.url' => 'http://127.0.0.1:11434', 'services.ollama.model' => 'qwen2.5:3b']);
        Http::fake([
            '127.0.0.1:11434/api/chat' => Http::response([
                'message' => [
                    'content' => json_encode([
                        'title' => 'Usage increased',
                        'summary' => 'Weekly active seats increased in the supplied product signal.',
                        'evidence_ids' => [$signal->id],
                    ], JSON_THROW_ON_ERROR),
                ],
            ]),
        ]);

        $this->actingAs($analyst)->post(route('accounts.insights.generate', $account))
            ->assertRedirect(route('accounts.show', $account));

        $this->assertDatabaseHas('insights', [
            'account_id' => $account->id,
            'title' => 'Usage increased',
            'status' => 'draft',
            'model_name' => 'qwen2.5:3b',
        ]);
    }

    public function test_model_cannot_save_an_insight_with_unknown_evidence_ids(): void
    {
        $analyst = User::factory()->create();
        $analyst->forceFill(['role' => 'analyst'])->save();
        $account = Account::create($this->accountData() + ['consent_status' => 'granted']);
        $account->signals()->create([
            'type' => 'usage',
            'source' => 'Product Analytics',
            'score' => 82,
            'recorded_at' => now(),
            'summary' => 'Weekly active seats increased.',
        ]);
        config(['services.ollama.url' => 'http://127.0.0.1:11434']);
        Http::fake([
            '127.0.0.1:11434/api/chat' => Http::response([
                'message' => [
                    'content' => json_encode([
                        'title' => 'Unsupported claim',
                        'summary' => 'This cites evidence that was not supplied.',
                        'evidence_ids' => [999999],
                    ], JSON_THROW_ON_ERROR),
                ],
            ]),
        ]);

        $this->actingAs($analyst)->from(route('accounts.show', $account))
            ->post(route('accounts.insights.generate', $account))
            ->assertSessionHasErrors('ai');

        $this->assertDatabaseCount('insights', 0);
    }

    public function test_account_briefing_saves_only_valid_signal_citations_and_audit_context(): void
    {
        $analyst = User::factory()->create();
        $analyst->forceFill(['role' => 'analyst'])->save();
        $account = Account::create($this->accountData() + ['consent_status' => 'granted']);
        $signal = $account->signals()->create([
            'type' => 'usage',
            'source' => 'Product Analytics',
            'score' => 82,
            'recorded_at' => now(),
            'summary' => 'Weekly active seats increased.',
        ]);
        config(['services.ollama.url' => 'http://127.0.0.1:11434', 'services.ollama.model' => 'qwen2.5:3b']);
        Http::fake([
            '127.0.0.1:11434/api/chat' => Http::response([
                'message' => [
                    'content' => json_encode([
                        'answer' => 'Weekly active seats increased according to the product analytics signal.',
                        'signal_ids' => [$signal->id],
                    ], JSON_THROW_ON_ERROR),
                ],
            ]),
        ]);

        $this->actingAs($analyst)->post(route('accounts.brief', $account), [
            'question' => 'What changed in product usage?',
        ])->assertRedirect(route('accounts.show', $account))
            ->assertSessionHas('briefing.answer', 'Weekly active seats increased according to the product analytics signal.');

        $this->assertDatabaseHas('account_activity', [
            'account_id' => $account->id,
            'user_id' => $analyst->id,
            'event_type' => 'briefing_generated',
        ]);
    }

    public function test_account_contact_can_be_added_without_primary_checkbox_and_pending_consent_is_not_dated(): void
    {
        $analyst = User::factory()->create();
        $analyst->forceFill(['role' => 'analyst'])->save();
        $account = Account::create($this->accountData());

        $this->actingAs($analyst)->post(route('accounts.contacts.store', $account), [
            'name' => 'Jordan Lee',
            'title' => 'Operations lead',
            'email' => 'jordan@example.test',
            'consent_status' => 'pending',
        ])->assertRedirect();

        $this->assertDatabaseHas('account_contacts', [
            'account_id' => $account->id,
            'email' => 'jordan@example.test',
            'is_primary' => false,
            'consent_status' => 'pending',
            'consent_updated_at' => null,
        ]);
    }

    public function test_account_search_and_filters_cover_signals_and_playbooks(): void
    {
        $analyst = User::factory()->create();
        $analyst->forceFill(['role' => 'analyst'])->save();
        $account = Account::create($this->accountData());
        $account->signals()->create([
            'type' => 'usage',
            'source' => 'Product Analytics',
            'score' => 72,
            'recorded_at' => now(),
            'summary' => 'Seat activation declined this month.',
        ]);
        $account->actions()->create([
            'created_by' => $analyst->id,
            'title' => 'Review renewal readiness',
            'owner' => $analyst->name,
            'playbook' => 'Renewal readiness',
            'status' => 'open',
        ]);

        $this->actingAs($analyst)->get(route('accounts.index', ['q' => 'activation declined']))
            ->assertOk()
            ->assertSee($account->name);

        $this->get(route('accounts.index', ['playbook' => 'Renewal readiness']))
            ->assertOk()
            ->assertSee($account->name);

        $this->get(route('accounts.index', ['signal_type' => 'usage', 'health_min' => 90]))
            ->assertOk()
            ->assertDontSee($account->name);
    }

    public function test_unassessed_imported_account_can_be_reviewed_without_inventing_missing_values(): void
    {
        $analyst = User::factory()->create();
        $analyst->forceFill(['role' => 'analyst'])->save();
        $account = Account::create(array_replace($this->accountData(), [
            'annual_revenue' => null,
            'health_score' => null,
            'status' => 'needs_review',
            'renewal_date' => null,
            'engagement_status' => 'unknown',
            'opportunity_stage' => null,
        ]));

        $this->actingAs($analyst)->get(route('accounts.edit', $account))
            ->assertOk()
            ->assertSee('Not recorded');

        $this->actingAs($analyst)->put(route('accounts.update', $account), [
            'name' => $account->name,
            'industry' => $account->industry,
            'owner' => $account->owner,
            'annual_revenue' => null,
            'health_score' => null,
            'status' => 'needs_review',
            'renewal_date' => null,
            'subscription_plan' => null,
            'contract_length_months' => null,
            'engagement_status' => 'unknown',
            'segment' => 'unclassified',
            'expansion_value' => 0,
            'forecast_revenue' => null,
            'opportunity_stage' => null,
            'consent_status' => 'pending',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'annual_revenue' => null,
            'health_score' => null,
            'renewal_date' => null,
            'opportunity_stage' => null,
        ]);
    }

    public function test_revenue_view_includes_accounts_without_recorded_pipeline_stage(): void
    {
        $viewer = User::factory()->create();
        $viewer->forceFill(['role' => 'analyst'])->save();
        $account = Account::create(array_replace($this->accountData(), [
            'annual_revenue' => null,
            'health_score' => null,
            'status' => 'needs_review',
            'renewal_date' => null,
            'engagement_status' => 'unknown',
            'opportunity_stage' => null,
        ]));

        $this->actingAs($viewer)->get(route('revenue.index', ['stage' => 'unassigned']))
            ->assertOk()
            ->assertSee('Not recorded')
            ->assertSee($account->name);
    }


    public function test_sitemap_contains_only_public_routes(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml')
            ->assertSee(route('product'), false)
            ->assertSee(route('about'), false)
            ->assertDontSee('/admin', false)
            ->assertDontSee('/accounts', false);
    }

    private function accountData(): array
    {
        return [
            'name' => 'Northstar Labs',
            'industry' => 'SaaS',
            'owner' => 'Nia Chen',
            'annual_revenue' => 1250000,
            'health_score' => 84,
            'status' => 'healthy',
            'renewal_date' => '2027-01-15',
        ];
    }
}
