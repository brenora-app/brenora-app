<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayPalCheckout
{
    public function isConfigured(): bool
    {
        return filled(config('services.paypal.client_id'))
            && filled(config('services.paypal.secret'));
    }

    public function createOrder(string $amount, string $currency, string $description, string $reference): array
    {
        return $this->request('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $reference,
                'custom_id' => $reference,
                'description' => $description,
                'amount' => [
                    'currency_code' => $currency,
                    'value' => $amount,
                ],
            ]],
        ]);
    }

    public function captureOrder(string $orderId): array
    {
        return $this->request('/v2/checkout/orders/'.$orderId.'/capture', []);
    }

    private function request(string $path, array $data): array
    {
        try {
            $tokenResponse = Http::asForm()
                ->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.secret'))
                ->post($this->baseUrl().'/v1/oauth2/token', ['grant_type' => 'client_credentials']);

            $tokenResponse->throw();
            $accessToken = $tokenResponse->json('access_token');
            if (! is_string($accessToken) || $accessToken === '') {
                throw new RuntimeException('The payment provider did not return an access token.');
            }

            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->post($this->baseUrl().$path, $data);

            $response->throw();

            return $response->json() ?? [];
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            Log::error('Payment provider request failed.', [
                'path' => $path,
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException('The payment provider could not complete the request. Please try again.', previous: $exception);
        }
    }

    private function baseUrl(): string
    {
        return config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }
}
