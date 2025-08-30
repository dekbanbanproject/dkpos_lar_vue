@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto p-4">
  <div class="flex items-center justify-between mb-3">
    <h1 class="text-xl font-semibold">รายจ่าย</h1>
    <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">+ บันทึกรายจ่าย</a>
  </div>

  @if(session('success')) <div class="alert alert-success mb-3">{{ session('success') }}</div> @endif

  <form method="get" class="flex flex-wrap gap-2 mb-3">
    <input type="date" name="from" value="{{ request('from') }}" class="input input-bordered">
    <input type="date" name="to"   value="{{ request('to')   }}" class="input input-bordered">
    <input type="text" name="category" value="{{ request('category') }}" placeholder="หมวด"
           class="input input-bordered">
    <button class="btn">กรอง</button>
  </form>

  <div class="card bg-base-100 shadow">
    <div class="overflow-x-auto">
      <table class="table">
        <thead>
          <tr>
            <th>วันที่</th><th>เลขที่</th><th>รายการ</th><th>หมวด</th><th class="text-right">จำนวนเงิน</th>
          </tr>
        </thead>
        <tbody>
          @foreach($expenses as $e)
          <tr>
            <td>{{ $e->spent_at->format('d/m/Y') }}</td>
            <td>{{ $e->ref_no }}</td>
            <td>{{ $e->title }}</td>
            <td>{{ $e->category ?? '-' }}</td>
            <td class="text-right">{{ number_format($e->amount, 2) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="p-3 flex items-center justify-between">
      <div>รวมช่วงที่เลือก: <b>{{ number_format($sum,2) }}</b> บาท</div>
      <div>{{ $expenses->links() }}</div>
    </div>
  </div>
</div>
@endsection
