<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signal extends Model
{
    protected $fillable = [
        'account_id',
        'type',
        'source',
        'score',
        'recorded_at',
        'summary',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
