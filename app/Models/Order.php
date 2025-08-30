<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_no','sub_total','discount','total','paid','change','payment_method','user_id',
        'customer_name','customer_phone','customer_address'
    ];
    public function items(){ return $this->hasMany(OrderItem::class); }
}
