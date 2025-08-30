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
            'items'            => ['required','array','min:1'],
            'items.*.product_id'=> ['required','integer','exists:products,id'],
            'items.*.qty'      => ['required','integer','min:1'],
            'discount'         => ['nullable','numeric','min:0'],
            'paid'             => ['nullable','numeric','min:0'],
            'payment_method'   => ['required','in:cash,qr,card'],
        ]);

        return DB::transaction(function () use ($data) {
            $map = collect($data['items'])->keyBy('product_id')->map(fn($r) => (int) $r['qty']);

            $products = Product::whereIn('id', $map->keys())
                ->lockForUpdate()->get()->keyBy('id');

            $items = [];
            $sub = 0;

            foreach ($map as $pid => $qty) {
                $p = $products[$pid];
                $qty = max(0, min($qty, $p->stock));
                if ($qty === 0) continue;

                $line = round($p->price * $qty, 2);
                $sub += $line;

                $items[] = [
                    'product_id' => $p->id,
                    'name'       => $p->name,
                    'price'      => $p->price,
                    'qty'        => $qty,
                    'total'      => $line,
                ];

                $p->decrement('stock', $qty);
            }

            $discount = min($data['discount'] ?? 0, $sub);
            $total    = max(0, $sub - $discount);
            $paid     = $data['paid'] ?? 0;
            $change   = max(0, $paid - $total);

            // บันทึกแบบตรง ๆ ด้วย DB::table (ไม่ต้องมีโมเดลก่อนก็ได้)
            $orderId = DB::table('orders')->insertGetId([
                'order_no'       => now()->format('YmdHis').'-'.rand(100,999),
                'sub_total'      => $sub,
                'discount'       => $discount,
                'total'          => $total,
                'paid'           => $paid,
                'change'         => $change,
                'payment_method' => $data['payment_method'],
                'user_id'        => auth('web')->id(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            foreach ($items as $it) {
                DB::table('order_items')->insert([
                    'order_id'  => $orderId,
                    'product_id'=> $it['product_id'],
                    'name'      => $it['name'],
                    'price'     => $it['price'],
                    'qty'       => $it['qty'],
                    'total'     => $it['total'],
                    'created_at'=> now(),
                    'updated_at'=> now(),
                ]);
            }

            return response()->json([
                'message' => 'สร้างออเดอร์สำเร็จ',
                'order'   => [
                    'id'         => $orderId,
                    'order_no'   => DB::table('orders')->where('id',$orderId)->value('order_no'),
                    'items'      => $items,
                    'sub_total'  => $sub,
                    'discount'   => $discount,
                    'total'      => $total,
                    'paid'       => $paid,
                    'change'     => $change,
                ],
            ]);
        });
    }
}