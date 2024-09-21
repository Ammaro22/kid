<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Subject::create(['name' => 'خبرة علمية']);
        Subject::create(['name' => 'خبرة لغوية']);
        Subject::create(['name' => 'ديانة']);
        Subject::create(['name' => 'خبرة رياضية']);
        Subject::create(['name' => 'F']);
        Subject::create(['name' => 'E']);
        Subject::create(['name' => 'أكل']);
        Subject::create(['name' => 'رسم']);
        Subject::create(['name' => 'رياضة']);
        Subject::create(['name' => 'سلوك']);


    }
}
