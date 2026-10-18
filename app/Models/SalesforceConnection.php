<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesforceConnection extends Model
{
    protected $fillable = [
        'access_token',
        'refresh_token',
        'instance_url',
        'last_synced_at',
        'connected_by',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'last_synced_at' => 'datetime',
        ];
    }
}
