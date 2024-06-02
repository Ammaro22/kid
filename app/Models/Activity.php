<?php

namespace App\Models;

use App\Traits\Imageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{

    use HasFactory,Imageable;
    protected $table = 'activities';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'name',
        'date',
        'post'
    ];

    public function imageِs(){
        return $this->hasMany(Image::class,'activity_id');
    }

    public function fileِs(){
        return $this->hasMany(File::class,'activity_id');
    }
}
