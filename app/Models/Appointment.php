<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    protected $table = 'appointments';
    protected $primaryKey ='id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'the_day',
        'the_time',
        'status'
    ];

    ///////////////////////////////////
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    //////////////////////
    public function reservation(){
        return $this->hasOne(Reservation::class);
    }
}
