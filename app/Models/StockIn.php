<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    protected $fillable = ['ref_no','supplier_name','received_at','note','user_id'];

    public function items()   { return $this->hasMany(StockInItem::class); }
    public function user()    { return $this->belongsTo(User::class); }

    // ช่วยนับรวม
    public function getTotalQtyAttribute() {
        return $this->items->sum('qty');
    }
}
