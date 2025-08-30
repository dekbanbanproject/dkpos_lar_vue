<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRevision extends Model
{
  protected $fillable = ['order_id','user_id','reason','before','after'];
  protected $casts = ['before'=>'array','after'=>'array'];

  public function order(){ return $this->belongsTo(Order::class); }
  public function user(){ return $this->belongsTo(User::class); }

  public function revisions(){ return $this->hasMany(\App\Models\OrderRevision::class); }

}
