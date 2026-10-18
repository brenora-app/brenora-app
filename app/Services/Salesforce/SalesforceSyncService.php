<?php

namespace App\Services\Salesforce;

use App\Models\Account;
use App\Models\SalesforceConnection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class SalesforceSyncService
{
    /**
     * @return array{accounts: int, contacts: int}
     */
    public function sync(SalesforceConnection $connection): array
    {
        $accounts = $this->query($connection, 'SELECT Id, Name, Industry, AnnualRevenue, Owner.Name FROM Account');
        $salesforceAccounts = [];

        foreach ($accounts as $record) {
            if (! is_string($record['Id'] ?? null)
                || ! preg_match('/^[a-zA-Z0-9]{15,18}$/', $record['Id'])
                || ! is_string($record['Name'] ?? null)
                || trim($record['Name']) === '') {
                continue;
            }

            $account = Account::query()->firstOrNew(['salesforce_id' => $record['Id']]);
            $isNew = ! $account->exists;
            $account->fill([
                'name' => mb_substr($record['Name'], 0, 255),
                'industry' => is_string($record['Industry'] ?? null) ? mb_substr($record['Industry'], 0, 120) : null,
                'owner' => is_string(data_get($record, 'Owner.Name')) ? mb_substr(data_get($record, 'Owner.Name'), 0, 255) : null,
                'annual_revenue' => is_numeric($record['AnnualRevenue'] ?? null) ? max(0, (float) $record['AnnualRevenue']) : null,
                'salesforce_synced_at' => now(),
            ]);
            if ($isNew) {
                $account->fill([
                    'health_score' => null,
                    'status' => 'needs_review',
                    'engagement_status' => 'unknown',
                    'segment' => 'unclassified',
                    'expansion_value' => null,
                    'opportunity_stage' => null,
                    'consent_status' => 'pending',
                    'consent_updated_at' => null,
                ]);
            }
            $account->save();
            $salesforceAccounts[$record['Id']] = $account;
        }

        $contactsSynced = 0;
        foreach (array_chunk(array_keys($salesforceAccounts), 100) as $ids) {
            $quotedIds = collect($ids)->map(fn (string $id): string => "'{$id}'")->implode(',');
            $contactRecords = $this->query(
                $connection,
                'SELECT Id, AccountId, Name, Title, Email, Phone FROM Contact WHERE AccountId IN ('.$quotedIds.')',
            );

            foreach ($contactRecords as $record) {
                $account = $salesforceAccounts[$record['AccountId'] ?? ''] ?? null;
                if (! $account
                    || ! is_string($record['Id'] ?? null)
                    || ! preg_match('/^[a-zA-Z0-9]{15,18}$/', $record['Id'])
                    || ! is_string($record['Name'] ?? null)
                    || trim($record['Name']) === '') {
                    continue;
                }

                $contact = $account->contacts()->firstOrNew(['salesforce_id' => $record['Id']]);
                $isNew = ! $contact->exists;
                $contact->fill([
                    'name' => mb_substr($record['Name'], 0, 160),
                    'title' => isset($record['Title']) ? mb_substr((string) $record['Title'], 0, 120) : null,
                    'email' => isset($record['Email']) ? mb_substr((string) $record['Email'], 0, 254) : null,
                    'phone' => isset($record['Phone']) ? mb_substr((string) $record['Phone'], 0, 40) : null,
                ]);
                if ($isNew) {
                    $contact->fill([
                        'is_primary' => false,
                        'consent_status' => 'pending',
                        'consent_updated_at' => null,
                    ]);
                }
                $contact->save();
                $contactsSynced++;
            }
        }

        foreach ($salesforceAccounts as $account) {
            $account->activity()->create([
                'user_id' => $connection->connected_by,
                'event_type' => 'salesforce_sync',
                'summary' => 'Account and contact profile synchronized from Salesforce.',
                'details' => ['synced_at' => now()->toIso8601String()],
            ]);
        }

        $connection->forceFill(['last_synced_at' => now()])->save();

        return ['accounts' => count($salesforceAccounts), 'contacts' => $contactsSynced];
    }

    public function refreshAccessToken(SalesforceConnection $connection): void
    {
        $response = Http::asForm()->acceptJson()->post(rtrim((string) config('services.salesforce.login_url'), '/').'/services/oauth2/token', [
            'grant_type' => 'refresh_token',
            'client_id' => config('services.salesforce.client_id'),
            'client_secret' => config('services.salesforce.client_secret'),
            'refresh_token' => $connection->refresh_token,
        ])->throw();

        $token = $response->json('access_token');
        if (! is_string($token) || $token === '') {
            throw new UnexpectedValueException('Salesforce did not return a refreshed access token.');
        }

        $instanceUrl = $response->json('instance_url', $connection->instance_url);
        $this->assertTrustedInstanceUrl($instanceUrl);
        $connection->forceFill([
            'access_token' => $token,
            'instance_url' => $instanceUrl,
            'refresh_token' => $response->json('refresh_token', $connection->refresh_token),
        ])->save();
    }

    private function query(SalesforceConnection $connection, string $soql): array
    {
        $path = '/services/data/'.rawurlencode((string) config('services.salesforce.api_version', 'v60.0')).'/query';
        $url = rtrim($connection->instance_url, '/').$path;
        $records = [];

        do {
            $response = $this->get($connection, $url, $path === '/services/data/'.config('services.salesforce.api_version', 'v60.0').'/query'
                ? ['q' => $soql]
                : []);
            $payload = $response->json();
            if (! is_array($payload) || ! is_array($payload['records'] ?? null)) {
                throw new UnexpectedValueException('Salesforce returned an invalid query response.');
            }
            array_push($records, ...$payload['records']);
            $next = $payload['nextRecordsUrl'] ?? null;
            if ($next === null) {
                $url = '';
            } elseif (is_string($next) && str_starts_with($next, '/services/data/')) {
                $url = rtrim($connection->instance_url, '/').$next;
            } else {
                throw new UnexpectedValueException('Salesforce returned an invalid pagination path.');
            }
        } while ($url !== '');

        return $records;
    }

    private function get(SalesforceConnection $connection, string $url, array $query = []): Response
    {
        $response = Http::withToken($connection->access_token)->acceptJson()->get($url, $query);
        if ($response->status() === 401) {
            $this->refreshAccessToken($connection);
            $response = Http::withToken($connection->access_token)->acceptJson()->get($url, $query);
        }

        return $response->throw();
    }

    public function assertTrustedInstanceUrl(mixed $url): void
    {
        if (! is_string($url)) {
            throw new UnexpectedValueException('Salesforce returned an invalid instance URL.');
        }
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (($parts['scheme'] ?? null) !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || ! preg_match('/^[a-z0-9.-]+\.salesforce\.com$/', $host)) {
            throw new UnexpectedValueException('Salesforce returned an untrusted instance URL.');
        }
    }
}
