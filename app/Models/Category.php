<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'color', 'icon', 'is_system'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Scope for user's categories (including global/system ones)
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)->orWhere('is_system', true);
    }
}