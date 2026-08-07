<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Floor Cleaners', 'slug' => 'floor-cleaner', 'sort_order' => 1],
            ['name' => 'Phenyl', 'slug' => 'phenyl', 'sort_order' => 2],
            ['name' => 'Room Fresheners', 'slug' => 'room-freshener', 'sort_order' => 3],
            ['name' => 'Toilet & Bathroom Care', 'slug' => 'toilet-bathroom-care', 'sort_order' => 4],
            ['name' => 'Kitchen & Dish Care', 'slug' => 'kitchen-dish-care', 'sort_order' => 5],
            ['name' => 'Laundry Care', 'slug' => 'laundry-care', 'sort_order' => 6],
            ['name' => 'Specialty Surface Cleaners', 'slug' => 'specialty-surface-cleaners', 'sort_order' => 7],
            ['name' => 'Personal Care', 'slug' => 'personal-care', 'sort_order' => 8],
            ['name' => 'Feminine Hygiene', 'slug' => 'feminine-hygiene', 'sort_order' => 9],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category + ['is_active' => true]
            );
        }
    }
}
