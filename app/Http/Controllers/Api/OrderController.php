<?php
namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\{Order, OrderItem, Product, Payment};


class OrderController extends Controller
{
        public function store(Request $request)
        {
            $data = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.qty' => 'required|integer|min:1',
                'discount' => 'nullable|numeric|min:0',
                'paid' => 'required|numeric|min:0',
                'payment_method' => 'nullable|string|max:50',
                'customer_name' => 'nullable|string|max:255',
            ]);


            $itemsInput = collect($data['items']);
            $products = Product::whereIn('id', $itemsInput->pluck('product_id'))->get()->keyBy('id');


            $lines = [];
            $subTotal = 0;
            foreach ($itemsInput as $row) {
                $p = $products[$row['product_id']] ?? null;
                    if (!$p) { return response()->json(['message' => 'Product not found'], 422); }
                    if ($p->stock < $row['qty']) {
                    return response()->json(['message' => "สต็อกไม่พอ: {$p->name}"], 422);
                }
                $lineTotal = $p->price * $row['qty'];
                $subTotal += $lineTotal;
                    $lines[] = [
                        'product' => $p,
                        'qty' => (int)$row['qty'],
                        'price' => (float)$p->price,
                        'total' => (float)$lineTotal,
                    ];
            }


            $discount = min((float)($data['discount'] ?? 0), $subTotal);
            $total = $subTotal - $discount;
            $paid = (float)$data['paid'];
            if ($paid + 0.0001 < $total) {
                return response()->json(['message' => 'ยอดชำระน้อยกว่ายอดรวม'], 422);
            }
            $change = $paid - $total;
            $method = $data['payment_method'] ?? 'cash';


            return DB::transaction(function () use ($lines, $subTotal, $discount, $total, $paid, $change, $method, $data) {
            $sequence = Order::whereDate('created_at', now()->toDateString())->count() + 1;
            $orderNo = 'BK'.now()->format('ymd').str_pad($sequence, 4, '0', STR_PAD_LEFT);


            $order = Order::create([
                'order_no' => $orderNo,
                'sub_total' => $subTotal,
                'discount' => $discount,
                'total' => $total,
                'paid' => $paid,
                'change' => $change,
                'payment_method' => $method,
                'status' => 'paid',
                'customer_name' => $data['customer_name'] ?? null,
            ]);


            foreach ($lines as $l) {
                    OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $l['product']->id,
                    'name' => $l['product']->name,
                    'price' => $l['price'],
                    'qty' => $l['qty'],
                    'total' => $l['total'],
                ]);

                // ตัดสต็อก
                $l['product']->decrement('stock', $l['qty']);
            }
        }
}