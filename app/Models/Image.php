<?php

namespace App\Models;
use App\Traits\Imageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{

    use HasFactory,Imageable;
    protected $table = 'images';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'name',
        'path',
        'activity_id'
    ];

    public function Act(){
        return $this->belongsTo(Activity::class,'activity_id');
    }
}
