<?php

namespace Database\Seeders;

use App\Models\Days_week;
use App\Models\Evaluation_Criteria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EvaluationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Evaluation_Criteria::create(['evaluation_criterias' => 'الإدارة الصفية']);
        Evaluation_Criteria::create(['evaluation_criterias' => 'جودة المعلم']);
        Evaluation_Criteria::create(['evaluation_criterias' => 'مشاركة الطلاب']);
        Evaluation_Criteria::create(['evaluation_criterias' => 'الاحتراف']);
    }
}
