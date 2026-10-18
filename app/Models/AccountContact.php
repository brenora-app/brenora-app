<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountContact extends Model
{
    protected $fillable = [
        'account_id',
        'salesforce_id',
        'name',
        'title',
        'email',
        'phone',
        'is_primary',
        'consent_status',
        'consent_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'consent_updated_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
