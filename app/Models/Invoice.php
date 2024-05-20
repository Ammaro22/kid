<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $table = 'invoices';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'batch',
        'student_id'
    ];

    public function Studen2(){
        return $this->belongsTo(Student::class,'student_id');
    }
}
