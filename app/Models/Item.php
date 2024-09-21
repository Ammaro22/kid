<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $table = 'items';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'image',
        'item_name',
        'item_description'
    ];
}
