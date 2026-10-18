<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CommercialAction;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('DemoDataSeeder is restricted to local and testing environments.');
        }

        $user = User::firstOrCreate(
            ['email' => 'demo@brenora.com'],
            [
                'name' => 'Demo Operator',
                'password' => Hash::make('Demo@1234'),
            ]
        );
        $user->forceFill(['role' => 'admin'])->save();

        $accounts = [
            [
                'name' => 'Northstar Labs',
                'industry' => 'SaaS',
                'owner' => 'Nia Chen',
                'annual_revenue' => 1250000,
                'health_score' => 84,
                'status' => 'expanding',
                'renewal_date' => '2026-12-15',
            ],
            [
                'name' => 'Harbor Grid',
                'industry' => 'Logistics',
                'owner' => 'Ari Solomon',
                'annual_revenue' => 940000,
                'health_score' => 71,
                'status' => 'at_risk',
                'renewal_date' => '2026-11-08',
            ],
            [
                'name' => 'Lattice Peak',
                'industry' => 'Fintech',
                'owner' => 'Emilia Cruz',
                'annual_revenue' => 1680000,
                'health_score' => 91,
                'status' => 'healthy',
                'renewal_date' => '2027-02-06',
            ],
        ];

        foreach ($accounts as $accountData) {
            $account = Account::firstOrCreate(
                ['name' => $accountData['name']],
                $accountData
            );

            $signals = [
                [
                    'type' => 'usage',
                    'source' => 'Product Analytics',
                    'score' => 88,
                    'recorded_at' => now()->subDay(),
                    'summary' => 'Weekly active seats increased 18% after onboarding refresh.',
                ],
                [
                    'type' => 'renewal',
                    'source' => 'CRM',
                    'score' => 75,
                    'recorded_at' => now()->subHours(8),
                    'summary' => 'Renewal forecast remains positive with two expansion deals in motion.',
                ],
            ];

            foreach ($signals as $signalData) {
                $account->signals()->firstOrCreate(
                    [
                        'type' => $signalData['type'],
                        'source' => $signalData['source'],
                    ],
                    [
                        'source' => $signalData['source'],
                        'score' => $signalData['score'],
                        'summary' => $signalData['summary'],
                    ]
                );
            }

            Insight::firstOrCreate(
                [
                    'account_id' => $account->id,
                    'title' => $account->status === 'at_risk' ? 'Renewal attention needed' : 'Customer momentum overview',
                ],
                [
                    'summary' => $account->status === 'at_risk'
                        ? 'Review the upcoming renewal and confirm the latest customer priorities with the account owner.'
                        : 'Recent account signals are available for a human-reviewed customer briefing.',
                    'severity' => $account->status === 'at_risk' ? 'attention' : 'information',
                    'status' => 'draft',
                    'model_name' => 'Demo data (not model-generated)',
                    'evidence' => $account->signals()->pluck('id')->map(fn ($id) => 'Signal #'.$id)->all(),
                ]
            );

            $action = CommercialAction::firstOrCreate(
                [
                    'account_id' => $account->id,
                    'title' => 'Prepare renewal check-in',
                ],
                [
                    'created_by' => $user->id,
                    'description' => 'Confirm current account priorities and document the next customer conversation.',
                    'owner' => $account->owner,
                    'due_date' => $account->renewal_date,
                    'status' => $account->status === 'at_risk' ? 'pending_approval' : 'open',
                    'approval_required' => $account->status === 'at_risk',
                ]
            );

            $action->events()->firstOrCreate(
                ['to_status' => $action->status, 'from_status' => null],
                ['user_id' => $user->id, 'note' => 'Seeded demo action.']
            );
        }
    }
}
