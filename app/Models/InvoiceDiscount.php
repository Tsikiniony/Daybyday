<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceDiscount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'discount_rate_id',
        'original_amount',
        'discounted_amount'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function discountRate()
    {
        return $this->belongsTo(DiscountRate::class);
    }
}