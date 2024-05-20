<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    protected $table = 'subjects';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'name',

    ];
    public function evaluation2(){
        return $this->hasMany(Evaluation::class,'subject_id');
    }

    public function ds(){
        return $this->hasMany(Day_Subject::class,'subject_id');
    }
}
