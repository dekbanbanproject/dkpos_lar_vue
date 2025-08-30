@extends('layouts.app')
@section('content')
<div class="max-w-xl mx-auto p-4">
  <h1 class="text-xl font-semibold mb-3">บันทึกรายจ่าย</h1>

  <form method="POST" action="{{ route('admin.expenses.store') }}" class="space-y-3">
    @csrf
    <div>
      <label class="label">เลขที่เอกสาร</label>
      <input name="ref_no" value="{{ $ref }}" class="input input-bordered w-full" required>
    </div>
    <div>
      <label class="label">วันที่จ่าย</label>
      <input type="date" name="spent_at" value="{{ now()->toDateString() }}"
             class="input input-bordered w-full" required>
    </div>
    <div>
      <label class="label">รายการ</label>
      <input name="title" class="input input-bordered w-full" required>
    </div>
    <div>
      <label class="label">หมวด</label>
      <input name="category" class="input input-bordered w-full" placeholder="เช่น วัตถุดิบ / ค่าแรง">
    </div>
    <div>
      <label class="label">จำนวนเงิน</label>
      <input type="number" step="0.01" min="0" name="amount" class="input input-bordered w-full text-right" required>
    </div>
    <div>
      <label class="label">หมายเหตุ</label>
      <input name="note" class="input input-bordered w-full">
    </div>

    <div class="flex justify-end gap-2">
      <a href="{{ route('admin.expenses.index') }}" class="btn btn-ghost">ยกเลิก</a>
      <button class="btn btn-primary">บันทึก</button>
    </div>
  </form>
</div>
@endsection
