<?php

namespace App\Models;

use App\Traits\Imageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory,Imageable;

    protected $table = 'files';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'name',
        'path',
        'activity_id'
    ];

    public function Act1(){
        return $this->belongsTo(Activity::class,'activity_id');
    }
}
