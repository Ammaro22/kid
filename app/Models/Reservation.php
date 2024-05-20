<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $table = 'reservations';
    protected $primaryKey ='id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'appointment_id',
        'description',
        'status'
    ];
    /////////////////////////////////////
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    ///////////////////////////////
    public function appointment(){
        return $this->belongsTo(Appointment::class);
    }
}
