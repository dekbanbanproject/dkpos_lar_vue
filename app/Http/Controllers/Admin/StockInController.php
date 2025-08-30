<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInItem;

class StockInController extends Controller
{
    public function index()
    {
        $docs = StockIn::with('user')->latest()->paginate(20);
        return view('admin.stock_ins.index', compact('docs'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get(['id','name','sku','barcode','stock']);
        $ref = now()->format('GRYmdHis').'-'.rand(100,999);
        return view('admin.stock_ins.create', compact('products','ref'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ref_no' => ['required','string','max:50','unique:stock_ins,ref_no'],
            'supplier_name' => ['nullable','string','max:255'],
            'received_at' => ['required','date'],
            'note' => ['nullable','string','max:2000'],
            'items' => ['required','array','min:1'],
            'items.*.product_id' => ['required','integer','exists:products,id'],
            'items.*.qty' => ['required','integer','min:1'],
            'items.*.cost' => ['nullable','numeric','min:0'],
        ]);

        $items = collect($data['items'])->groupBy('product_id')->map(fn($g) => [
            'product_id' => (int)$g->first()['product_id'],
            'qty'        => (int)$g->sum('qty'),
            'cost'       => (float)optional($g->last())['cost'] ?? null,
        ])->values();

        DB::transaction(function () use ($data, $items, &$doc) {
            $doc = StockIn::create([
                'ref_no'        => $data['ref_no'],
                'supplier_name' => $data['supplier_name'] ?? null,
                'received_at'   => $data['received_at'],
                'note'          => $data['note'] ?? null,
                'user_id'       => auth('web')->id(),
            ]);

            $products = Product::whereIn('id', $items->pluck('product_id'))
                ->lockForUpdate()->get()->keyBy('id');

            foreach ($items as $it) {
                $p = $products[$it['product_id']];
                $p->increment('stock', $it['qty']);

                StockInItem::create([
                    'stock_in_id' => $doc->id,
                    'product_id'  => $p->id,
                    'qty'         => $it['qty'],
                    'cost'        => $it['cost'],
                ]);
            }
        });

        return redirect()->route('admin.stock-ins.show', $doc->id)
            ->with('success', 'บันทึกรับสินค้าเรียบร้อย');
    }

    public function show(StockIn $stock_in)
    {
        $stock_in->load(['items.product','user']);
        return view('admin.stock_ins.show', ['doc' => $stock_in]);
    }
}
