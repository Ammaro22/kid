<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    use HasFactory;
    protected $table = 'homework';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'the_day',
        'Subject',
        'homework',
        'category_id'
    ];
    public function category2(){
        return $this->belongsTo(Category::class,'category_id');
    }
}
