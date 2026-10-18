<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountCustomField extends Model
{
    protected $fillable = [
        'name',
        'key',
        'type',
        'options',
        'required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'required' => 'boolean',
        ];
    }
}
