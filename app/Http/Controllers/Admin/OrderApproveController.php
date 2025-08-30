<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Controllers\Web\CheckoutController;

class OrderApproveController extends Controller
{
    public function index()
    {
        $orders = Order::whereIn('status',['awaiting_payment','pending'])
            ->latest()->paginate(20);
        return view('admin.orders_pending', compact('orders'));
    }

    public function approve(Order $order, CheckoutController $checkout)
    {
        $checkout->markPaid($order, (float)($order->payment_amount ?? $order->total), 'ADMIN-OK');
        return back()->with('ok','อนุมัติการชำระแล้ว');
    }
}
