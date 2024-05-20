<?php

namespace App\Models;

use App\Traits\Imageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image_child extends Model
{
    use HasFactory,Imageable;
    protected $table = 'image_children';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'name',
        'path',
        'student_id'
    ];
    public function studn(){
        return $this->belongsTo(Student::class,'student_id');
    }
}
