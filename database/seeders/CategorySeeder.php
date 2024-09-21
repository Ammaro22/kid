<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Category::create(['name' => 'P_Kg']);
        Category::create(['name' => 'Kg1']);
        Category::create(['name' => 'Kg2']);
        Category::create(['name' => 'Kg3']);

    }
}
