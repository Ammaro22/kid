<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation_Teacher extends Model
{
    use HasFactory;
    protected $table = 'evaluation__teachers';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'note',
        'user_id',
        'evaluation',
        'evaluation_criterias_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function evaluation_criterias()
    {
        return $this->belongsTo(Evaluation_Criteria::class,'evaluation_criterias_id');
    }
}
