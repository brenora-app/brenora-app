<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountActivity extends Model
{
    protected $table = 'account_activity';

    protected $fillable = [
        'account_id',
        'user_id',
        'event_type',
        'summary',
        'details',
    ];

    protected function casts(): array
    {
        return ['details' => 'array'];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
