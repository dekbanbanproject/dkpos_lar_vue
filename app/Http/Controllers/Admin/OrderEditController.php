<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\OrderRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderEditController extends Controller
{
  public function edit(Order $order)
  {
    $order->load('items.product');
    $products = Product::orderBy('name')->get(['id','name','price','stock']);
    return view('admin.orders_edit', compact('order','products'));
  }

  public function update(Request $req, Order $order)
  {
    $data = $req->validate([
      'items' => 'required|array|min:1',
      'items.*.product_id' => 'required|exists:products,id',
      'items.*.qty'  => 'required|integer|min:1',
      'items.*.price'=> 'required|numeric|min:0',
      'reason' => 'nullable|string|max:500',
    ], [], ['items.*.product_id'=>'สินค้า','items.*.qty'=>'จำนวน','items.*.price'=>'ราคา']);

    if ($order->status === 'cancelled') {
      return back()->withErrors(['msg'=>'ไม่สามารถแก้ไขออเดอร์ที่ยกเลิกแล้ว'])->withInput();
    }

    DB::transaction(function () use ($order, $data, $req) {
      $order->load('items');

      // map เดิม [pid => qty]
      $oldQty = [];
      foreach ($order->items as $it) $oldQty[$it->product_id] = ($oldQty[$it->product_id] ?? 0) + $it->qty;

      // รวมรายการซ้ำ และ map ใหม่ [pid => {qty, price}]
      $newMap = [];
      foreach ($data['items'] as $r) {
        $pid = (int)$r['product_id'];
        $newMap[$pid] = [
          'qty'   => ($newMap[$pid]['qty']   ?? 0) + (int)$r['qty'],
          'price' => (float)$r['price'], // ใช้ราคาชิ้นล่าสุด
        ];
      }

      // ปรับสต็อกตาม delta
      $pids = array_unique(array_merge(array_keys($oldQty), array_keys($newMap)));
      foreach ($pids as $pid) {
        $new = $newMap[$pid]['qty'] ?? 0;
        $old = $oldQty[$pid] ?? 0;
        $delta = $new - $old;            // เพิ่มขาย -> ตัดสต็อก / ลดขาย -> คืนสต็อก
        if ($delta > 0) {
          Product::where('id',$pid)->decrement('stock', $delta);
        } elseif ($delta < 0) {
          Product::where('id',$pid)->increment('stock', -$delta);
        }
      }

      // before snapshot
      $before = [
        'items' => $order->items->map(fn($i)=>[
          'product_id'=>$i->product_id,'name'=>$i->name,'qty'=>$i->qty,'price'=>$i->price,'total'=>$i->total
        ])->values(),
        'sub_total'=>$order->sub_total,
        'discount' =>$order->discount,
        'total'    =>$order->total,
      ];

      // เขียน items ใหม่
      $order->items()->delete();
      $sub = 0;
      foreach ($newMap as $pid => $v) {
        $p    = Product::find($pid);
        $qty  = $v['qty'];
        $price= $v['price'];
        $line = $qty * $price;
        $sub += $line;

        OrderItem::create([
          'order_id'  => $order->id,
          'product_id'=> $pid,
          'name'      => $p->name ?? 'Item',
          'price'     => $price,
          'qty'       => $qty,
          'total'     => $line,
        ]);
      }

      $order->update([
        'sub_total' => $sub,
        'total'     => $sub - ($order->discount ?? 0),
      ]);

      // after snapshot
      $after = [
        'items' => $order->items()->get()->map(fn($i)=>[
          'product_id'=>$i->product_id,'name'=>$i->name,'qty'=>$i->qty,'price'=>$i->price,'total'=>$i->total
        ])->values(),
        'sub_total'=>$order->sub_total,
        'discount' =>$order->discount,
        'total'    =>$order->total,
      ];

      OrderRevision::create([
        'order_id' => $order->id,
        'user_id'  => optional($req->user('web'))->id,
        'reason'   => $req->input('reason'),
        'before'   => $before,
        'after'    => $after,
      ]);
    });

    return redirect()->route('admin.orders.edit', $order)->with('ok','บันทึกการแก้ไขเรียบร้อย');
  }
}
