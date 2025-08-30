<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Category;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $products = Product::with('category')
            ->when($q !== '', fn($qq) =>
                $qq->where('name','like',"%{$q}%")
                   ->orWhere('sku','like',"%{$q}%")
                   ->orWhere('barcode','like',"%{$q}%")
            )
            ->latest()->paginate(12)->withQueryString();

        return view('admin.products.index', compact('products', 'q'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['nullable','exists:categories,id'],
            'name'        => ['required','string','max:255'],
            'sku'         => ['nullable','string','max:100','unique:products,sku'],
            'barcode'     => ['nullable','string','max:100','unique:products,barcode'],
            'price'       => ['required','numeric','min:0'],
            'cost_price'  => ['nullable','numeric','min:0'],
            'stock'       => ['required','integer','min:0'],
            'image'       => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'is_active'   => ['nullable','boolean'],
        ]);

        $isActive = $request->boolean('is_active');

        $imagePath = null;
        if ($request->hasFile('image')) {
            // เก็บใน storage/app/public/products → เข้าถึงผ่าน /storage/products/xxx
            $p = $request->file('image')->store('products', 'public');
            $imagePath = 'storage/'.$p;
        }

        $product = Product::create([
            'category_id' => $data['category_id'] ?? null,
            'name'        => $data['name'],
            'sku'         => $data['sku'] ?? null,
            'barcode'     => $data['barcode'] ?? null,
            'price'       => $data['price'],
            'cost_price'  => $data['cost_price'] ?? null,
            'stock'       => $data['stock'],
            'image_path'  => $imagePath,
            'is_active'   => $isActive,
            // 'is_active'   => (bool)($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.products.index')->with('ok', 'บันทึกสินค้าเรียบร้อย');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.edit', compact('product','categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => ['nullable','exists:categories,id'],
            'name'        => ['required','string','max:255'],
            'sku'         => ['nullable','string','max:100','unique:products,sku,'.$product->id],
            'barcode'     => ['nullable','string','max:100','unique:products,barcode,'.$product->id],
            'price'       => ['required','numeric','min:0'],
            'cost_price'  => ['nullable','numeric','min:0'],
            'stock'       => ['required','integer','min:0'],
            'image'       => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'is_active'   => ['nullable','boolean'],
        ]);

        $isActive = $request->boolean('is_active'); 

        if ($request->hasFile('image')) {
            // ลบรูปเก่า (ถ้ามี)
            if ($product->image_path) {
                $old = str_replace('storage/', '', $product->image_path);
                Storage::disk('public')->delete($old);
            }
            $p = $request->file('image')->store('products', 'public');
            $product->image_path = 'storage/'.$p;
        }

        $product->fill([
            'category_id' => $data['category_id'] ?? null,
            'name'        => $data['name'],
            'sku'         => $data['sku'] ?? null,
            'barcode'     => $data['barcode'] ?? null,
            'price'       => $data['price'],
            'cost_price'  => $data['cost_price'] ?? null,
            'stock'       => $data['stock'],
            'is_active'   => $isActive,
            // 'is_active'   => (bool)($data['is_active'] ?? true),
        ])->save();

        return redirect()->route('admin.products.index')->with('ok', 'แก้ไขสินค้าเรียบร้อย');
    }

    public function destroy(Product $product)
    {
        if ($product->image_path) {
            $old = str_replace('storage/', '', $product->image_path);
            Storage::disk('public')->delete($old);
        }
        $product->delete();

        return back()->with('ok', 'ลบสินค้าเรียบร้อย');
    }
}
