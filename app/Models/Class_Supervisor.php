<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Class_Supervisor extends Model
{
    use HasFactory;

    protected $table = 'class__supervisors';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'user_id',
        'category_id'
    ];
    public function user1(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function categor1(){
        return $this->belongsTo(Category::class);
    }
}
