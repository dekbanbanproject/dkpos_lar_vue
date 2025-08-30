@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-4">
  <h1 class="text-xl font-semibold mb-3">หมวดหมู่สินค้า</h1>

  @if(session('ok')) <div class="alert alert-success mb-3">{{ session('ok') }}</div> @endif
  @if($errors->any()) <div class="alert alert-error mb-3">{{ $errors->first() }}</div> @endif

  <div class="card bg-base-100 shadow mb-4">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.categories.store') }}" class="grid md:grid-cols-3 gap-3 items-end">
        @csrf
        <div>
          <label class="label">ชื่อหมวดหมู่</label>
          <input name="name" class="input input-bordered w-full" required>
        </div>
        <div>
          <label class="label">ลำดับ (position)</label>
          <input type="number" name="position" value="0" class="input input-bordered w-full">
        </div>
        <div>
          <button class="btn btn-primary w-full">เพิ่มหมวดหมู่</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card bg-base-100 shadow">
    <div class="card-body overflow-x-auto p-0">
      <table class="table">
        <thead>
          <tr>
            <th>ลำดับ</th><th>ชื่อ</th><th>Slug</th><th class="text-right">จำนวนสินค้า</th><th class="text-right">จัดการ</th>
          </tr>
        </thead>
        <tbody>
        @forelse($categories as $c)
          <tr>
            <td>{{ $c->position }}</td>
            <td>{{ $c->name }}</td>
            <td class="opacity-70">{{ $c->slug }}</td>
            <td class="text-right">{{ $c->products_count }}</td>
            <td class="text-right">
              <div class="flex justify-end gap-2">
                <a href="{{ route('admin.categories.edit',$c) }}" class="btn btn-sm">แก้ไข</a>
                <form method="POST" action="{{ route('admin.categories.destroy',$c) }}"
                      onsubmit="return confirm('ยืนยันลบหมวดนี้?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-error">ลบ</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center py-10 opacity-60">ยังไม่มีหมวดหมู่</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-3">{{ $categories->links() }}</div>
  </div>
</div>
@endsection
