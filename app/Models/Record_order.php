<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record_order extends Model
{
    use HasFactory;
    protected $table = 'record_orders';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
      'student_id',
        'accept'
    ];
    public function stud(){
        return $this->belongsTo(Student_before_accept::class,'student_id');
    }

    public function student1(){
        return $this->hasOne(Student::class,'record_order_id');
    }


}
