<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'transaction_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    // Relations (optional)
    public function user()  { return $this->belongsTo(\App\Models\User::class); }
    public function order() { return $this->belongsTo(\App\Models\Order::class); }
}
