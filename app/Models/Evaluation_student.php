<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation_student extends Model
{
    use HasFactory;
    protected $table = 'evaluation_students';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'student_id',
        'Evaluation'
    ];
    public function Studen()
    {
        return $this->belongsTo(Student::class,'student_id');
    }

}
