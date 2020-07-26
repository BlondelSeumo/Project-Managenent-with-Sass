<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    protected $fillable = [
        'order_id',
        'invoice_id',
        'currency',
        'amount',
        'txn_id',
        'payment_status',
        'receipt',
        'client_id'
    ];
}
