<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceT extends Model
{
    use HasFactory;
    protected $table = 'attendance_t_s';
    protected $primaryKey ='id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'the_date',
        'present'
    ];
    ///////////belongsTo users////////////
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
