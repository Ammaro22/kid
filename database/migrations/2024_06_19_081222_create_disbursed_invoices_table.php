<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disbursed_invoices', function (Blueprint $table) {
            $table->id();
            $table->double('price');
            $table->foreignId('invoice_type_id')->constrained('invoice_types')->cascadeOnDelete();
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disbursed_invoices');
    }
};
