<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoice_type extends Model
{
    use HasFactory;
    protected $table = 'invoice_types';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'name',
        'description'
    ];
    public function d_invoice(){
        return $this->hasMany(Disbursed_invoice::class,'invoice_type_id');
    }
}
