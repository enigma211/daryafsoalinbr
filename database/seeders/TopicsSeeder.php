<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class TopicsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 24; $i++) {
            Category::firstOrCreate(['topic' => 'مبحث ' . $i]);
        }
    }
}
