<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutController extends Controller
{
    public function show()
    {
        return view('shop.checkout');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'    => 'required|string|max:100',
            'customer_phone'   => 'required|string|max:30',
            'customer_address' => 'nullable|string|max:500',
            'payment_method'   => 'required|in:cod,transfer,qr,card',
            'cart_json'        => 'required|string',
        ]);

        $cart = json_decode($data['cart_json'], true) ?? [];
        if (!is_array($cart) || count($cart) === 0) {
            return back()->withErrors(['cart_json' => 'ตะกร้าว่าง'])->withInput();
        }

        // เตรียมข้อมูลสินค้า
        $ids = collect($cart)->pluck('id')->unique()->all();
        $products = Product::whereIn('id', $ids)->get()->keyBy('id');

        $items = [];
        $sub_total = 0;

        foreach ($cart as $c) {
            $p = $products[$c['id']] ?? null;
            if (!$p) return back()->withErrors(['cart_json' => 'มีสินค้าบางรายการไม่พบ'])->withInput();

            $qty = max(1, (int)($c['qty'] ?? 1));
            if ($p->stock < $qty) {
                return back()->withErrors(['cart_json' => "สต็อกสินค้า \"{$p->name}\" ไม่พอ (คงเหลือ {$p->stock})"])->withInput();
            }

            $price = (float)$p->price;
            $line  = $price * $qty;
            $sub_total += $line;

            $items[] = compact('p','qty','price','line');
        }

        $order = DB::transaction(function () use ($items, $sub_total, $data) {
            $order = Order::create([
                'order_no'         => 'W'.now()->format('YmdHis').'-'.random_int(100,999),
                'sub_total'        => $sub_total,
                'discount'         => 0,
                'total'            => $sub_total,
                'paid'             => 0,
                'change'           => 0,
                'payment_method'   => $data['payment_method'],
                'user_id'          => null,
                'customer_name'    => $data['customer_name'] ?? null,
                'customer_phone'   => $data['customer_phone'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
            ]);

            foreach ($items as $it) {
                OrderItem::create([
                    'order_id'  => $order->id,
                    'product_id'=> $it['p']->id,
                    'name'      => $it['p']->name,
                    'price'     => $it['price'],
                    'qty'       => $it['qty'],
                    'total'     => $it['line'],
                ]);
                $it['p']->decrement('stock', $it['qty']); // หักสต็อก
            }

            return $order;
        });

        return redirect()->route('shop.checkout.done', $order->id);
    }

    public function done($id)
    {
        $order = Order::with('items')->findOrFail($id);
        return view('shop.checkout_done', compact('order'));
    }
}
