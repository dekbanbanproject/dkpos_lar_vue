@php($editing = isset($product))
@csrf
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
  <div class="md:col-span-2 space-y-3">
    <div class="form-control">
      <label class="label">ชื่อสินค้า</label>
      <input name="name" class="input input-bordered" required
             value="{{ old('name', $product->name ?? '') }}">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <div class="form-control">
        <label class="label">ราคา</label>
        <input name="price" type="number" step="0.01" min="0" class="input input-bordered" required
               value="{{ old('price', $product->price ?? 0) }}">
      </div>
      <div class="form-control">
        <label class="label">ต้นทุน (ถ้ามี)</label>
        <input name="cost_price" type="number" step="0.01" min="0" class="input input-bordered"
               value="{{ old('cost_price', $product->cost_price ?? '') }}">
      </div>
      <div class="form-control">
        <label class="label">สต็อก</label>
        <input name="stock" type="number" min="0" class="input input-bordered" required
               value="{{ old('stock', $product->stock ?? 0) }}">
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <div class="form-control">
        <label class="label">SKU</label>
        <input name="sku" class="input input-bordered"
               value="{{ old('sku', $product->sku ?? '') }}">
      </div>
      <div class="form-control">
        <label class="label">บาร์โค้ด</label>
        <input name="barcode" class="input input-bordered"
               value="{{ old('barcode', $product->barcode ?? '') }}">
      </div>
      <div class="form-control">
        {{-- <label class="label">หมวดหมู่</label>
        <select name="category_id" class="select select-bordered">
          <option value="">- ไม่ระบุ -</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id ?? '') == $c->id)>
              {{ $c->name }}
            </option>
          @endforeach
        </select> --}}
        <label class="label">หมวดหมู่</label>
          <select name="category_id" class="select select-bordered w-full">
            <option value="">-- ไม่ระบุหมวด --</option>
            @foreach(\App\Models\Category::orderBy('position')->orderBy('name')->get() as $cat)
              <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id ?? null)==$cat->id)>
                {{ $cat->name }}
              </option>
            @endforeach
          </select>

      </div>
    </div>

    {{-- <div class="form-control">
      <label class="label cursor-pointer justify-start gap-3">
        <input type="checkbox" name="is_active" class="checkbox"
               @checked(old('is_active', ($product->is_active ?? true)))>
        <span>เปิดขาย</span>
      </label>
    </div>
  </div> --}}
  <div class="form-control">
  <label class="label cursor-pointer justify-start gap-3">
    {{-- ถ้าไม่เช็ก จะส่งค่า 0 --}}
    <input type="hidden" name="is_active" value="0">
    {{-- ถ้าเช็ก จะส่งค่า 1 (ทับค่า 0 ข้างบน) --}}
    <input type="checkbox" name="is_active" value="1" class="checkbox"
           @checked(old('is_active', $product->is_active ?? true))>
    <span>เปิดขาย</span>
  </label>
</div>


  <div class="space-y-3">
    <div class="form-control">
      <label class="label">รูปสินค้า</label>
      <input type="file" name="image" accept="image/*" class="file-input file-input-bordered" onchange="previewImg(event)">
    </div>
    <div class="rounded-xl overflow-hidden bg-base-200">
      <img id="preview" class="w-full object-cover"
           src="{{ $editing && $product->image_path ? asset($product->image_path) : 'https://placehold.co/600x400?text=Preview' }}">
    </div>
  </div>
</div>

<div class="mt-4 flex gap-2">
  <a href="{{ route('admin.products.index') }}" class="btn">ยกเลิก</a>
  <button class="btn btn-primary">{{ $editing ? 'บันทึกการแก้ไข' : 'บันทึกสินค้า' }}</button>
</div>

@if ($errors->any())
  <div class="alert alert-error mt-4">
    {{ implode(' | ', $errors->all()) }}
  </div>
@endif

<script>
  function previewImg(e){
    const img = document.getElementById('preview');
    const f = e.target.files?.[0];
    if (!f) return;
    img.src = URL.createObjectURL(f);
  }
</script>
