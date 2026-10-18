<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_marketing_pages_show_the_shared_sections(): void
    {
        foreach ([route('home'), route('product'), route('about'), route('pricing')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Launch')
                ->assertSee('Growth')
                ->assertSee('Enterprise')
                ->assertSee('$99.99')
                ->assertSee('$189.99')
                ->assertSee('2 months free')
                ->assertSee('Frequently asked')
                ->assertSee('Let’s make customer signals useful.')
                ->assertSee('Product principles')
                ->assertSee('Brenora (Pvt) Ltd')
                ->assertSee('Brenora LLC')
                ->assertSee('Start free trial')
                ->assertSee('Sign in to buy with PayPal')
                ->assertDontSee('sandbox')
                ->assertDontSee('paypal-button');
        }
    }

    public function test_payment_checkout_endpoints_require_an_authenticated_workspace_user(): void
    {
        $this->postJson('/paypal/orders', ['plan' => 'launch', 'period' => 'monthly'])->assertUnauthorized();
        $this->postJson('/paypal/orders/1/capture')->assertUnauthorized();
    }

    public function test_contact_form_stores_a_valid_submission(): void
    {
        $this->from(route('home'))->post(route('contact.store'), [
            'name' => 'Taylor Morgan',
            'email' => 'taylor@example.test',
            'company' => 'Northstar Labs',
            'topic' => 'enterprise',
            'message' => 'I would like to learn more about account workflows.',
        ])->assertRedirect(route('home'))
            ->assertSessionHas('contact_submitted', true);

        $this->assertDatabaseHas('contact_submissions', [
            'email' => 'taylor@example.test',
            'company' => 'Northstar Labs',
            'topic' => 'enterprise',
        ]);
    }

    public function test_contact_form_rejects_invalid_data(): void
    {
        $this->post(route('contact.store'), [
            'name' => '',
            'email' => 'not-an-email',
            'topic' => 'unknown',
            'message' => 'short',
        ])->assertSessionHasErrors(['name', 'email', 'topic', 'message']);

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_product_page_explains_the_current_stack_and_marks_future_options_as_conceptual(): void
    {
        $this->get(route('product'))
            ->assertOk()
            ->assertSee('PHP and Laravel application')
            ->assertSee('MySQL-backed')
            ->assertSee('NIM, RAPIDS, and Triton')
            ->assertSee('None is represented as deployed')
            ->assertSee('Application preview / 03')
            ->assertSee('brenora.net');
    }
}
