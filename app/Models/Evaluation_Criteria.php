<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation_Criteria extends Model
{
    use HasFactory;
    protected $table = 'evaluation__criterias';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'evaluation_criterias',
    ];
    public function evaluation_teacher(){
        return $this->hasMany(Evaluation_Teacher::class,'evaluation_criterias_id');
    }
}
