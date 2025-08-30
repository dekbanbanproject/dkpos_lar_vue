<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Controllers\Web\CheckoutController;
use Illuminate\Http\Request;

class OrderApproveController extends Controller

{
    // public function index()
    // {
    //     $orders = Order::whereIn('status',['awaiting_payment','pending'])
    //         ->latest()->paginate(20);
    //     return view('admin.orders_pending', compact('orders'));
    // }

    // public function approve(Order $order, CheckoutController $checkout)
    // {
    //     $checkout->markPaid($order, (float)($order->payment_amount ?? $order->total), 'ADMIN-OK');
    //     return back()->with('ok','อนุมัติการชำระแล้ว');
    // }
     public function index(Request $r)
    {
        // ค่าดีฟอลต์: รอจ่าย/รอตรวจสลิป
        $status = $r->get('status', 'awaiting_payment,pending'); // ใส่ all เพื่อดูทั้งหมด
        $search = trim((string)$r->get('q', ''));

        $q = Order::query();

        if ($status !== 'all') {
            $statuses = array_filter(array_map('trim', explode(',', $status)));
            $q->whereIn('status', $statuses);
        }

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('order_no', 'like', "%{$search}%")
                   ->orWhere('customer_name', 'like', "%{$search}%")
                   ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $orders = $q->latest()->paginate(20)->appends($r->query());
        return view('admin.orders_pending', compact('orders','status','search'));
    }

    public function approve(Order $order)
    {
        // อนุมัติให้เป็นชำระแล้ว (ตัวอย่าง: ใช้ยอด total)
        if ($order->status !== 'paid') {
            $order->load('items');
            $order->update([
                'status'          => 'paid',
                'paid'            => $order->total,
                'payment_amount'  => $order->total,
                'paid_at'         => now(),
            ]);
            // หักสต็อก (ถ้ายังไม่ได้หัก)
            foreach ($order->items as $it) {
                \App\Models\Product::where('id', $it->product_id)->decrement('stock', $it->qty);
            }
        }
        return back()->with('ok','อนุมัติการชำระแล้ว');
    }
}
