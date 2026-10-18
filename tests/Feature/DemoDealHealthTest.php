<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDealHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_demo_page_describes_the_current_demo_and_proposed_nvidia_stack(): void
    {
        $this->get(route('demo.deal-health'))
            ->assertOk()
            ->assertSee('At-risk enterprise renewal')
            ->assertSee('Upsell candidate')
            ->assertSee('Unresponsive buyer')
            ->assertSee('Explore an evidence-first deal review')
            ->assertSee('NVIDIA NIM')
            ->assertSee('The public playground presents a transparent, text-based signal review.')
            ->assertSee('Use fictional data only')
            ->assertSee('Try our demo');
    }

    public function test_analysis_returns_direct_evidence_and_suggested_actions_without_persisting_notes(): void
    {
        $notes = 'Renewal call: our champion left the company last month. The security review is still unanswered. Product usage declined over the last six weeks.';

        $response = $this->postJson(route('demo.deal-health.analyze'), ['notes' => $notes])
            ->assertOk()
            ->assertJsonPath('health', 'Elevated risk')
            ->assertJsonPath('risks.0.label', 'Customer champion may have left')
            ->assertJsonPath('risks.0.evidence', 'Renewal call: our champion left the company last month.')
            ->assertJsonPath('risks.1.label', 'Security or compliance concern is open')
            ->assertJsonPath('risks.1.evidence', 'The security review is still unanswered.')
            ->assertJsonPath('risks.2.label', 'Product usage is declining')
            ->assertJsonPath('metrics.input_words', 23)
            ->assertJsonStructure(['score', 'risks', 'opportunities', 'recommendations', 'outreach', 'metrics' => ['processing_ms', 'input_words'], 'notice']);

        $this->assertDatabaseCount('contact_submissions', 0);
        $this->assertDatabaseCount('checkout_payments', 0);
        $this->assertStringNotContainsString($notes, json_encode($response->json('outreach')));
    }

    public function test_opportunity_scenario_flags_usage_and_seat_capacity_with_evidence(): void
    {
        $this->postJson(route('demo.deal-health.analyze'), [
            'notes' => 'Usage grew 38% this quarter and licenses are near the seat cap.',
        ])->assertOk()
            ->assertJsonPath('opportunities.0.label', 'Product usage is growing')
            ->assertJsonPath('opportunities.1.label', 'Licenses are near capacity')
            ->assertJsonPath('score', 90);
    }

    public function test_analysis_validates_input_and_is_available_without_authentication(): void
    {
        $this->postJson(route('demo.deal-health.analyze'), ['notes' => 'too short'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('notes');

        $this->assertGuest();
    }
}
