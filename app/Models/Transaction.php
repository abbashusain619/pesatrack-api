<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'type',
        'amount',
        'description',
        'reference',
        'transaction_date',
        'is_synced',
        'status',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'is_synced' => 'boolean',
        'status' => 'string',
    ];

    // Relationship with Account
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}