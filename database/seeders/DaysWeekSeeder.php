<?php

namespace Database\Seeders;

use App\Models\Days_week;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DaysWeekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Days_week::create(['name' => 'الأحد']);
        Days_week::create(['name' => 'الأثنين']);
        Days_week::create(['name' => 'الثلاثاء']);
        Days_week::create(['name' => 'الأريعاء']);
        Days_week::create(['name' => 'الخميس']);


    }
}
