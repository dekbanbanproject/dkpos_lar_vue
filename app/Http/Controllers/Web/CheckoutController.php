<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\PromptPay;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // from package

class CheckoutController extends Controller
{
    public function show()
    {
        return view('shop.checkout'); // ใช้ไฟล์ที่คุณมีอยู่แล้ว
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

        $method = $data['payment_method'];
        $status = in_array($method, ['qr','transfer']) ? 'awaiting_payment' : 'pending';

        $order = DB::transaction(function () use ($items, $sub_total, $data, $status, $method) {
            $order = Order::create([
                'order_no'         => 'W'.now()->format('YmdHis').'-'.random_int(100,999),
                'sub_total'        => $sub_total,
                'discount'         => 0,
                'total'            => $sub_total,
                'paid'             => 0,
                'change'           => 0,
                'payment_method'   => $method,
                'status'           => $status,
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
                // ยังไม่หักสต็อกจนกว่าจะชำระ (หรือจะหักเลยแล้วคืนเมื่อยกเลิกก็ได้)
            }

            return $order;
        });

        if (in_array($method, ['qr','transfer'])) {
            // ไปหน้า “ชำระเงิน/อัปสลิป”
            return redirect()->route('shop.checkout.pay', $order);
        }

        // COD/บัตร (ตัวอย่าง): ทำเป็น paid ทันที + หักสต็อก
        $this->markPaid($order, $order->total, 'COD');
        return redirect()->route('shop.checkout.done', $order);
    }

    /** หน้าชำระเงิน: QR + อัปโหลดสลิป + Poll สถานะ */
    public function pay(Order $order)
    {
        abort_if($order->status === 'cancelled', 404);

        // สร้าง PromptPay Payload
        // ตั้ง PromptPay ร้านของคุณตรงนี้ (เลือก 1 แบบ)
        // $target = PromptPay::toE164Mobile('08xxxxxxxx'); // ถ้าใช้เบอร์
        // $payload = PromptPay::mobile($target, (float)$order->total, $order->order_no);

        // หรือถ้าใช้เลขบัตรประชาชน 13 หลัก:
        // $payload = PromptPay::taxId('1234567890123', (float)$order->total, $order->order_no);

        // ⛳️ ตัวอย่าง: ใช้เบอร์พร้อมเพย์ (แทนด้วยของจริง)
        $payload = PromptPay::mobile(PromptPay::toE164Mobile('0812345678'), (float)$order->total, $order->order_no);

        // สร้าง QR (SVG หรือ PNG ก็ได้)
        $qrSvg = QrCode::format('svg')->size(280)->generate($payload);

        return view('shop.pay_qr', compact('order','qrSvg','payload'));
    }

    /** อัปโหลดสลิปโอนเงิน */
    public function uploadSlip(Request $request, Order $order)
    {
        abort_if(!in_array($order->status, ['awaiting_payment','pending']), 403);

        $valid = $request->validate([
            'slip'   => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $path = $request->file('slip')->store("slips/{$order->order_no}", 'public');

        $order->update([
            'payment_slip_path' => $path,
            'payment_amount'    => $valid['amount'] ?? null,
            'payment_ref'       => $order->order_no,
        ]);

        return back()->with('ok','อัปโหลดสลิปเรียบร้อย รอตรวจสอบ');
    }

    /** Poll: ส่งสถานะออเดอร์ */
    public function status(Order $order)
    {
        return response()->json(['status' => $order->status]);
    }

    /** หน้าสำเร็จ */
    public function done(Order $order)
    {
        $order->load('items');
        return view('shop.checkout_done', compact('order'));
    }

    /** ===== Utilities ===== */

    /** ให้แอดมินหรือ webhook เรียกเมื่อชำระแล้ว */
    public function markPaid(Order $order, float $amount, string $ref = null): void
    {
        if ($order->status === 'paid') return;

        DB::transaction(function () use ($order, $amount, $ref) {
            $order->update([
                'status'        => 'paid',
                'paid'          => $amount,
                'change'        => 0,
                'payment_amount'=> $amount,
                'payment_ref'   => $ref ?? $order->order_no,
                'paid_at'       => Carbon::now(),
            ]);

            // หักสต็อกหลังชำระ
            foreach ($order->items as $it) {
                Product::where('id', $it->product_id)->decrement('stock', $it->qty);
            }
        });
    }
}
