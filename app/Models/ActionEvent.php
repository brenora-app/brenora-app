<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionEvent extends Model
{
    protected $fillable = [
        'commercial_action_id',
        'user_id',
        'from_status',
        'to_status',
        'note',
    ];

    public function action(): BelongsTo
    {
        return $this->belongsTo(CommercialAction::class, 'commercial_action_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
