<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommercialAction extends Model
{
    protected $fillable = [
        'account_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'playbook',
        'owner',
        'due_date',
        'status',
        'approval_required',
        'outcome',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'approval_required' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ActionEvent::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
