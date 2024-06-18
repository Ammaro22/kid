<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $table = 'attendances';
    protected $primaryKey ='id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'student_id',
        'the_date',
        'status'
    ];

    ///////////belongsTo users////////////
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /////////////////belongsTo students///////////////////////
    public function student()
    {
        return $this->belongsTo(Student::class,'student_id');
    }
}
