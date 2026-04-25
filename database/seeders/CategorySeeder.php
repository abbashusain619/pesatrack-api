<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Food & Dining', 'color' => '#EF4444', 'icon' => '🍔'],
            ['name' => 'Transport', 'color' => '#F97316', 'icon' => '🚗'],
            ['name' => 'Utilities', 'color' => '#FACC15', 'icon' => '💡'],
            ['name' => 'Rent / Mortgage', 'color' => '#22C55E', 'icon' => '🏠'],
            ['name' => 'Health', 'color' => '#06B6D4', 'icon' => '🏥'],
            ['name' => 'Entertainment', 'color' => '#8B5CF6', 'icon' => '🎬'],
            ['name' => 'Shopping', 'color' => '#EC4899', 'icon' => '🛍️'],
            ['name' => 'Salary', 'color' => '#14B8A6', 'icon' => '💰'],
            ['name' => 'Investment', 'color' => '#6366F1', 'icon' => '📈'],
            ['name' => 'Other', 'color' => '#6B7280', 'icon' => '📦'],
        ];

        foreach ($categories as $cat) {
            Category::create(array_merge($cat, ['is_system' => true, 'user_id' => null]));
        }
    }
}