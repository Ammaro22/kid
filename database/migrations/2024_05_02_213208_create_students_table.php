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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('date_birth');
            $table->string('gender');
            $table->string('place_birth');
            $table->string('number_brother');
            $table->string('arrangement_in_family');
            $table->string('name_father');
            $table->string('name_mother');
            $table->string('father_academic_qualification');
            $table->string('mother_academic_qualification');
            $table->string('father_work');
            $table->string('mother_work');
            $table->string('home_address');
            $table->string('father_phone');
            $table->string('mother_phone');
            $table->string('landline_phone');
            $table->string('chronic_diseases');
            $table->string('type_allergies');
            $table->string('medicines_for_child');
            $table->string('dealing_with_heat');
            $table->string('preferred_name');
            $table->string('favorite_color');
            $table->string('favorite_game');
            $table->string('favorite_meal');
            $table->string('daytime_bedtime');
            $table->string('night_sleep_time');
            $table->string('relationship_with_strangers');
            $table->string('relationship_with_children');
            $table->string('photo_family_book');
            $table->string('photo_father_page');
            $table->string('photo_mother_page');
            $table->string('photo_child_page');
            $table->string('photo_father_identity');
            $table->string('photo_mother_identity');
            $table->string('photo_vaccine_card');
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
