<?php

// app/Http/Controllers/Admin/CategoryController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')
            ->orderBy('position')->orderBy('name')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'position' => 'nullable|integer|min:0',
        ]);
        $slug = Str::slug($data['name']);
        if (Category::where('slug',$slug)->exists()) { $slug .= '-'.time(); }

        Category::create([
            'name' => $data['name'],
            'slug' => $slug,
            'position' => $data['position'] ?? 0,
        ]);
        return back()->with('ok','เพิ่มหมวดหมู่แล้ว');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $r, Category $category)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'position' => 'nullable|integer|min:0',
        ]);
        $slug = Str::slug($data['name']);
        if (Category::where('slug',$slug)->where('id','!=',$category->id)->exists()) {
            $slug .= '-'.time();
        }
        $category->update([
            'name' => $data['name'],
            'slug' => $slug,
            'position' => $data['position'] ?? 0,
        ]);
        return redirect()->route('admin.categories.index')->with('ok','อัปเดตหมวดหมู่แล้ว');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['msg'=>'ลบไม่ได้: ยังมีสินค้าที่อยู่ในหมวดนี้']);
        }
        $category->delete();
        return back()->with('ok','ลบหมวดหมู่แล้ว');
    }
}
