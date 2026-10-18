<?php

return [
    'currency' => 'USD',
    'plans' => [
        'launch' => [
            'name' => 'Launch',
            'monthly' => '99.99',
            'annual' => '999.90',
            'description' => 'A clear customer workspace for a growing revenue team.',
            'features' => [
                'Account and contact profiles',
                'Customer signal timelines and source freshness',
                'Renewal dates, health context, and pipeline stages',
                'Evidence-linked account briefings',
                'Team tasks and activity history',
                'Role-based workspace access',
            ],
        ],
        'growth' => [
            'name' => 'Growth',
            'monthly' => '189.99',
            'annual' => '1899.90',
            'description' => 'More context and control for teams running a shared customer portfolio.',
            'features' => [
                'Everything in Launch',
                'Account segments and pipeline filtering',
                'Insight review, corrections, and evidence trails',
                'Consent and contact governance',
                'Forecast change history and renewal views',
                'Salesforce data import connection',
            ],
        ],
        'enterprise' => [
            'name' => null,
            'monthly' => null,
            'annual' => null,
            'description' => 'A considered deployment conversation for complex organizations.',
            'features' => [
                'Everything in Growth',
                'Discuss deployment and integration requirements',
                'Review governance and access needs together',
                'Plan a rollout around your existing workflows',
                'Scope commercial terms with our team',
            ],
        ],
    ],
];
