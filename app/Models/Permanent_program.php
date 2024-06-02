<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permanent_program extends Model
{
    use HasFactory;
    protected $table = 'permanent_programs';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'category_id',
    ];
    public function cat(){
        return $this->belongsTo(Category::class);
    }

    public function day_s(){
        return $this->hasMany(Day_Subject::class,'permanent_program_id');
    }
}
