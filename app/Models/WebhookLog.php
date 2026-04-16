<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
   protected $fillable = ['provider', 'payload', 'status'];

    protected $casts = [
        'payload' => 'array',
    ];
}
