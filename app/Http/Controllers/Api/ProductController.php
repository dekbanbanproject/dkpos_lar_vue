<?php
namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;


class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->query('search', ''));

        $query = Product::query()
            ->where('is_active', 1);

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('sku', 'like', "%{$q}%")
                   ->orWhere('barcode', 'like', "%{$q}%");
            });
        }

        $products = $query->orderBy('name')
            ->limit(100)
            ->get(['id','name','sku','barcode','price','stock','image_path']);

        return response()->json(['products' => $products]);
    }
}