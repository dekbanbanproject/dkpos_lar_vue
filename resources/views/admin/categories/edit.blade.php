@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto p-4">
  <h1 class="text-xl font-semibold mb-3">แก้ไขหมวดหมู่</h1>

  @if($errors->any()) <div class="alert alert-error mb-3">{{ $errors->first() }}</div> @endif

  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.categories.update',$category) }}" class="space-y-3">
        @csrf @method('PUT')
        <div>
          <label class="label">ชื่อหมวดหมู่</label>
          <input name="name" class="input input-bordered w-full" value="{{ $category->name }}" required>
        </div>
        <div>
          <label class="label">ลำดับ (position)</label>
          <input type="number" name="position" value="{{ $category->position }}" class="input input-bordered w-full">
        </div>
        <div class="flex justify-end gap-2">
          <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">ยกเลิก</a>
          <button class="btn btn-primary">บันทึก</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
