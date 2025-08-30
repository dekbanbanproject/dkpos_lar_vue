<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockInItem extends Model
{
    protected $fillable = ['stock_in_id','product_id','qty','cost'];

    public function stockIn() { return $this->belongsTo(StockIn::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
