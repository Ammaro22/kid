<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parents_note extends Model
{
    use HasFactory;
    protected $table = 'parents_notes';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'teacher_response',
        'parent_note',
        'homework_id',
        'user_id',
        'student_name'

    ];
    public function homework(){
        return $this->belongsTo(Homework::class,'homework_id');
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
