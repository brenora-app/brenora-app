<?php

namespace App\Http\Controllers;

use App\Models\SalesforceConnection;
use App\Services\Salesforce\SalesforceSyncService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use UnexpectedValueException;

class SalesforceIntegrationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('integrations.salesforce', [
            'connection' => SalesforceConnection::query()->first(),
            'configured' => filled(config('services.salesforce.client_id'))
                && filled(config('services.salesforce.client_secret')),
        ]);
    }

    public function connect(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $clientId = config('services.salesforce.client_id');
        $clientSecret = config('services.salesforce.client_secret');
        if (! filled($clientId) || ! filled($clientSecret)) {
            return back()->withErrors(['salesforce' => 'Configure the Salesforce client ID and secret in the server environment before connecting.']);
        }

        $loginUrl = $this->loginUrl();
        $redirectUri = config('services.salesforce.redirect_uri') ?: route('salesforce.callback');
        $state = Str::random(64);
        $request->session()->put('salesforce_oauth_state', $state);

        return redirect()->away($loginUrl.'/services/oauth2/authorize?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => 'api refresh_token',
        ]));
    }

    public function callback(Request $request, SalesforceSyncService $syncService): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $expectedState = $request->session()->pull('salesforce_oauth_state');
        abort_unless(is_string($expectedState) && hash_equals($expectedState, (string) $request->query('state')), 403);

        if ($request->filled('error')) {
            return redirect()->route('salesforce.index')->withErrors([
                'salesforce' => 'Salesforce connection was not authorized: '.mb_substr((string) $request->query('error_description', $request->query('error')), 0, 300),
            ]);
        }

        $validated = $request->validate(['code' => ['required', 'string', 'max:4096']]);
        try {
            $response = Http::asForm()->acceptJson()->post($this->loginUrl().'/services/oauth2/token', [
                'grant_type' => 'authorization_code',
                'code' => $validated['code'],
                'client_id' => config('services.salesforce.client_id'),
                'client_secret' => config('services.salesforce.client_secret'),
                'redirect_uri' => config('services.salesforce.redirect_uri') ?: route('salesforce.callback'),
            ])->throw();
            $payload = $response->json();
            if (! is_array($payload)
                || ! is_string($payload['access_token'] ?? null)
                || ! is_string($payload['refresh_token'] ?? null)
                || ! is_string($payload['instance_url'] ?? null)) {
                throw new UnexpectedValueException('Salesforce returned an incomplete OAuth response.');
            }
            $syncService->assertTrustedInstanceUrl($payload['instance_url']);
            $connection = SalesforceConnection::query()->first() ?? new SalesforceConnection;
            $connection->forceFill([
                'access_token' => $payload['access_token'],
                'refresh_token' => $payload['refresh_token'],
                'instance_url' => $payload['instance_url'],
                'connected_by' => $request->user()->id,
            ])->save();
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            Log::warning('Salesforce authorization failed', ['exception' => $exception::class]);

            return redirect()->route('salesforce.index')->withErrors([
                'salesforce' => 'Salesforce could not be connected. Check the connected-app configuration and authorize the requested API access.',
            ]);
        }

        return redirect()->route('salesforce.index')->with('status', 'Salesforce connected. Run a read-only sync when ready.');
    }

    public function sync(Request $request, SalesforceSyncService $syncService): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $connection = SalesforceConnection::query()->firstOrFail();

        try {
            $result = $syncService->sync($connection);
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            Log::warning('Salesforce read-only synchronization failed', [
                'exception' => $exception::class,
                'connection_id' => $connection->id,
            ]);

            return back()->withErrors(['salesforce' => 'Salesforce synchronization failed. Check API access, field permissions and the connection before retrying.']);
        }

        return back()->with('status', "Salesforce sync finished: {$result['accounts']} accounts and {$result['contacts']} contacts imported or updated.");
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        SalesforceConnection::query()->delete();

        return back()->with('status', 'Salesforce credentials removed. Imported account and contact records were retained.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->isAdmin(), 403);
    }

    private function loginUrl(): string
    {
        $url = (string) config('services.salesforce.login_url');
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        abort_unless(
            ($parts['scheme'] ?? null) === 'https'
                && (in_array($host, ['login.salesforce.com'], true) || preg_match('/^[a-z0-9-]+\.my\.salesforce\.com$/', $host)),
            500,
            'The Salesforce login URL is not an approved production authorization host.',
        );

        return rtrim($url, '/');
    }
}
