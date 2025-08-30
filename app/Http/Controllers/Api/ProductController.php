<?php
namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;


class ProductController extends Controller
{
public function index(Request $request)
{
$search = trim((string) $request->get('search'));


$q = Product::query()->where('is_active', true);
if ($search !== '') {
$q->where(function ($qq) use ($search) {
$qq->where('name', 'like', "%{$search}%")
->orWhere('sku', 'like', "%{$search}%")
->orWhere('barcode', 'like', "%{$search}%");
});
}


$products = $q->orderBy('name')->limit(100)->get([
'id','name','price','stock','image_path','sku','barcode'
]);


return response()->json(['products' => $products]);
}
}