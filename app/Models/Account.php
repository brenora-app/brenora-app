<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $casts = [
        'annual_revenue' => 'decimal:2',
        'renewal_date' => 'date',
        'custom_fields' => 'array',
        'consent_updated_at' => 'datetime',
        'salesforce_synced_at' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'industry',
        'owner',
        'annual_revenue',
        'health_score',
        'status',
        'renewal_date',
        'custom_fields',
        'subscription_plan',
        'contract_length_months',
        'engagement_status',
        'segment',
        'expansion_value',
        'forecast_revenue',
        'opportunity_stage',
        'consent_status',
        'consent_updated_at',
        'salesforce_id',
        'salesforce_synced_at',
    ];

    public function signals(): HasMany
    {
        return $this->hasMany(Signal::class);
    }

    public function insights(): HasMany
    {
        return $this->hasMany(Insight::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(CommercialAction::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(AccountContact::class);
    }

    public function activity(): HasMany
    {
        return $this->hasMany(AccountActivity::class);
    }
}
