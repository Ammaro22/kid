<?php

namespace App\Models;

use App\Traits\Imageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory,Imageable;
    protected $table = 'students';
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
        'record_order_id',
        'category_id',
        'user_id'

    ];

    public function Record(){
        return $this->hasMany(Record_order::class,'student_id');
    }
    public function image_c(){
        return $this->hasMany(Image_child::class,'student_id');
    }
    public function invoice(){
        return $this->hasMany(Invoice::class,'student_id');
    }
    public function evaluation1(){
        return $this->hasMany(Evaluation::class,'student_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    public function User()
    {
        return $this->belongsTo(User::class);
    }
    public function order()
    {
        return $this->belongsTo(Record_order::class);
    }
    //////////////Attendance///////////////
    public function attendance(){
        return $this->hasMany(Attendance::class,'student_id');
    }
}
