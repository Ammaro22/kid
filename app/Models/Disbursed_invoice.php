<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disbursed_invoice extends Model
{
    use HasFactory;
    protected $table = 'disbursed_invoices';
    protected $primaryKey ='id';
    public $timestamps = true;
    protected $fillable = [
        'price',
        'invoice_type_id'

    ];
    public function invoice_ty(){
        return $this->belongsTo(invoice_type::class,'invoice_type_id');
    }
}
