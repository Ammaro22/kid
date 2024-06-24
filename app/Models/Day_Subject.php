<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day_Subject extends Model
{
    use HasFactory;
    protected $table = 'day__subjects';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'days_week_id',
        'category_id',
        'subject_id'
    ];
    public function perm()
    {
        return $this->belongsTo(Permanent_program::class, 'permanent_program_id');
    }
    public function days_w(){
        return $this->belongsTo(Days_week::class,'days_week_id');
    }
    public function subje(){
        return $this->belongsTo(Subject::class,'subject_id');
    }
    public function cat(){
        return $this->belongsTo(Category::class,'category_id');
    }
}
