<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Days_week extends Model
{
    use HasFactory;
    protected $table = 'days_weeks';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'name',

    ];

    public function day_ss(){
        return $this->hasMany(Day_Subject::class,'days_week_id');
    }


}
