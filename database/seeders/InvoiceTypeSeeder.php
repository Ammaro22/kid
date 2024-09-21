<?php

namespace Database\Seeders;

use App\Models\invoice_type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        invoice_type::create(['name' => 'Occasions']);
        invoice_type::create(['name' => 'School Uniforms']);
        invoice_type::create(['name' => 'Stationery']);
        invoice_type::create(['name' => 'Other']);
    }

}
