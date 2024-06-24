<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'name',
    ];
    public function Program(){
        return $this->hasOne(Permanent_program::class,'category_id');
    }
    public function stu(){
        return $this->hasMany(Student::class,'category_id');
    }

    public function class_sav(){
        return $this->hasMany(Class_Supervisor::class,'category_id');
    }
    public function day_s(){
        return $this->hasMany(Day_Subject::class,'category_id');
    }
}
