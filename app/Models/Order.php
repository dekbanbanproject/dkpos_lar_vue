<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Order extends Model
{
use HasFactory;
protected $fillable = [
'order_no','sub_total','discount','total','paid','change','payment_method','status','customer_name'
];


public function items(){ return $this->hasMany(OrderItem::class); }
public function payments(){ return $this->hasMany(Payment::class); }
}