<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

  protected $table = 'users';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'first_name',
        'last_name',
        'password',
        'phone',
        'image',
        'age',
        'certificate',
        'salary',
        'Experience',
        'Previous_places_work',
        'role_id'
    ];


    public function role(){
        return $this->belongsTo(Role::class);
    }
  ///////////////////////////////
    public function appointments(){
        return $this->hasMany(Appointment::class,'user_id');
    }
    ///////////////////////////////////
    public function parent_note(){
        return $this->hasMany(Parents_note::class,'user_id');
    }
    /////////////////////////////////////////////////////////
    public function Student(){
        return $this->hasOne(Student::class,'user_id');
    }

    public function class_s(){
        return $this->hasMany(Class_Supervisor::class,'user_id');
    }

    public function reservstions(){
        return $this->hasMany(Reservation::class,'user_id');
    }

    ////////////////Attendance//////////////////
    public function attendances(){
        return $this->hasMany(Attendance::class,'user_id');
    }

    ////////////////AttendanceT//////////////////
    public function attendanceT(){
        return $this->hasMany(AttendanceT::class,'user_id');
    }

    public function evaluation_teacher(){
        return $this->hasMany(Evaluation_Teacher::class,'user_id');
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
