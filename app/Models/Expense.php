<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['ref_no','title','category','amount','spent_at','user_id','note'];
    protected $casts = ['spent_at' => 'date'];
    public function user(){ return $this->belongsTo(\App\Models\User::class); }
}
