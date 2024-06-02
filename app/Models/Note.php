<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;
    protected $table = 'notes';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'note_teacher',
        'note_admin',

    ];
    public function ev(){
        return $this->hasMany(Evaluation::class,'note_id');
    }
}
