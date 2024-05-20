<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;
    protected $table = 'evaluations';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'student_id',
        'subject_id',
        'note_id',
        'evaluation'
    ];

    public function Studen1()
    {
        return $this->belongsTo(Student::class,'student_id');
    }
    public function subject1()
    {
        return $this->belongsTo(Subject::class,'subject_id');
    }
    public function note1()
    {
        return $this->belongsTo(Note::class,'note_id');
    }

}
