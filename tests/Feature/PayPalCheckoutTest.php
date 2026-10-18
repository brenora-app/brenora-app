<?php

namespace Tests\Feature;

use App\Models\CheckoutPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPalCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.paypal.mode' => 'test',
            'services.paypal.client_id' => 'test-client',
            'services.paypal.secret' => 'test-secret',
        ]);
    }

    public function test_order_uses_the_server_defined_amount_and_persists_its_plan(): void
    {
        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'provider-token']),
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response(['id' => 'ORDER-123', 'status' => 'CREATED']),
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('paypal.orders.store'), [
            'plan' => 'launch',
            'period' => 'monthly',
            'amount' => '0.01',
        ])->assertOk()
            ->assertJsonPath('id', 'ORDER-123');

        $this->assertDatabaseHas('checkout_payments', [
            'user_id' => $user->id,
            'plan' => 'launch',
            'billing_period' => 'monthly',
            'amount' => '99.99',
            'currency' => 'USD',
            'paypal_order_id' => 'ORDER-123',
            'status' => 'created',
        ]);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/v2/checkout/orders')
            && $request->data()['purchase_units'][0]['amount']['value'] === '99.99');
    }

    public function test_signed_in_pricing_page_renders_checkout_buttons_without_exposing_server_credentials(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('pricing'))
            ->assertOk()
            ->assertSee('data-paypal-buttons', false)
            ->assertSee('data-plan="launch"', false)
            ->assertSee('data-plan="growth"', false)
            ->assertSee('data-period="monthly"', false)
            ->assertSee('test-client')
            ->assertDontSee('test-secret')
            ->assertDontSee('sandbox');
    }

    public function test_verified_capture_grants_paid_access_for_the_selected_term(): void
    {
        $user = User::factory()->create(['trial_ends_at' => now()->subDay()]);
        $payment = CheckoutPayment::create([
            'user_id' => $user->id,
            'plan' => 'growth',
            'billing_period' => 'annual',
            'amount' => '1899.90',
            'currency' => 'USD',
            'status' => 'created',
            'paypal_order_id' => 'ORDER-ANNUAL',
        ]);
        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'provider-token']),
            'api-m.sandbox.paypal.com/v2/checkout/orders/ORDER-ANNUAL/capture' => Http::response([
                'id' => 'ORDER-ANNUAL',
                'status' => 'COMPLETED',
                'purchase_units' => [[
                    'custom_id' => (string) $payment->id,
                    'amount' => ['currency_code' => 'USD', 'value' => '1899.90'],
                    'payments' => ['captures' => [[
                        'id' => 'CAPTURE-ANNUAL',
                        'status' => 'COMPLETED',
                        'amount' => ['currency_code' => 'USD', 'value' => '1899.90'],
                    ]]],
                ]],
            ]),
        ]);

        $this->actingAs($user)
            ->postJson(route('paypal.orders.capture', $payment))
            ->assertOk()
            ->assertJsonPath('status', 'completed');

        $this->assertDatabaseHas('checkout_payments', [
            'id' => $payment->id,
            'status' => 'completed',
            'paypal_capture_id' => 'CAPTURE-ANNUAL',
        ]);
        $this->assertSame('growth', $user->fresh()->paid_plan);
        $this->assertTrue($user->fresh()->paid_until->isFuture());

        $this->actingAs($user->fresh())->get('/dashboard')->assertOk();
    }

    public function test_invalid_capture_does_not_activate_paid_access(): void
    {
        $user = User::factory()->create(['trial_ends_at' => now()->subDay()]);
        $payment = CheckoutPayment::create([
            'user_id' => $user->id,
            'plan' => 'launch',
            'billing_period' => 'monthly',
            'amount' => '99.99',
            'currency' => 'USD',
            'status' => 'created',
            'paypal_order_id' => 'ORDER-WRONG-AMOUNT',
        ]);
        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'provider-token']),
            'api-m.sandbox.paypal.com/v2/checkout/orders/ORDER-WRONG-AMOUNT/capture' => Http::response([
                'id' => 'ORDER-WRONG-AMOUNT',
                'status' => 'COMPLETED',
                'purchase_units' => [[
                    'custom_id' => (string) $payment->id,
                    'amount' => ['currency_code' => 'USD', 'value' => '0.01'],
                    'payments' => ['captures' => [[
                        'id' => 'CAPTURE-WRONG-AMOUNT',
                        'status' => 'COMPLETED',
                        'amount' => ['currency_code' => 'USD', 'value' => '0.01'],
                    ]]],
                ]],
            ]),
        ]);

        $this->actingAs($user)
            ->postJson(route('paypal.orders.capture', $payment))
            ->assertUnprocessable();

        $this->assertNull($user->fresh()->paid_until);
        $this->assertDatabaseHas('checkout_payments', ['id' => $payment->id, 'status' => 'created']);
    }

    public function test_checkout_reports_missing_provider_credentials_without_creating_a_payment(): void
    {
        config(['services.paypal.client_id' => null, 'services.paypal.secret' => null]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('paypal.orders.store'), ['plan' => 'launch', 'period' => 'monthly'])
            ->assertServiceUnavailable()
            ->assertJsonPath('message', 'Online checkout is temporarily unavailable. Please contact our team.');

        $this->assertDatabaseCount('checkout_payments', 0);
    }
}
