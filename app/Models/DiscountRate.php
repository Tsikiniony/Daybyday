<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class DiscountRate extends Model
{
    use SoftDeletes;

    protected $fillable = ['rate', 'description', 'is_active'];

    public function invoiceDiscounts()
    {
        return $this->hasMany(InvoiceDiscount::class);
    }
}