<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;

class ShopApiController extends Controller
{
    public function categories()
    {
        // หมวด + จำนวนสินค้าแอคทีฟ
        $cats = Category::query()
            ->orderBy('name')
            ->get(['id','name']);

        // นับจำนวนแบบเร็ว
        $counts = Product::where('is_active',1)
            ->selectRaw('category_id, COUNT(*) as c')
            ->groupBy('category_id')->pluck('c','category_id');

        $out = $cats->map(fn($c)=>[
            'id'=>$c->id,'name'=>$c->name,'count'=> (int)($counts[$c->id] ?? 0)
        ]);

        return response()->json(['categories'=>$out]);
    }

    public function products(Request $req)
    {
        $q = trim((string)$req->query('search',''));
        $categoryId = $req->integer('category_id');
        $page   = max(1, (int)$req->query('page', 1));
        $per    = min(36, max(6, (int)$req->query('per_page', 12)));

        $query = Product::query()->where('is_active',1);

        if ($categoryId) $query->where('category_id', $categoryId);
        if ($q !== '') {
            $query->where(function($qq) use ($q){
                $qq->where('name','like',"%{$q}%")
                   ->orWhere('sku','like',"%{$q}%")
                   ->orWhere('barcode','like',"%{$q}%");
            });
        }

        $total = (clone $query)->count();

        $items = $query->orderBy('name')
            ->forPage($page, $per)
            ->get(['id','name','price','stock','image_path','sku','barcode']);

        return response()->json([
            'products'  => $items,
            'page'      => $page,
            'per_page'  => $per,
            'total'     => $total,
            'total_pages'=> (int)ceil($total / $per),
        ]);
    }
}
