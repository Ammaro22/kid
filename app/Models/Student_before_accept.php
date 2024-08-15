<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student_before_accept extends Model
{
    use HasFactory;
    protected $table = 'student_before_accepts';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'name',
        'date_birth',
        'gender',
        'place_birth',
        'number_brother',
        'arrangement_in_family',
        'name_father',
        'name_mother',
        'father_academic_qualification',
        'mother_academic_qualification',
        'father_work',
        'mother_work',
        'home_address',
        'father_phone',
        'mother_phone',
        'landline_phone',
        'chronic_diseases',
        'type_allergies',
        'medicines_for_child',
        'dealing_with_heat',
        'preferred_name',
        'favorite_color',
        'favorite_game',
        'favorite_meal',
        'daytime_bedtime',
        'night_sleep_time',
        'relationship_with_strangers',
        'relationship_with_children',
        'person_responsible_for_receiving',
        'person_who_fills_the_form',
        'photo_family_book',
        'photo_father_page',
        'photo_mother_page',
        'photo_child_page',
        'photo_father_identity',
        'photo_mother_identity',
        'photo_vaccine_card',
        'category_id',
        'user_id'

        
    ];

    public function image_s(){
        return $this->hasMany(Image_student::class,'student_id');
    }
}
