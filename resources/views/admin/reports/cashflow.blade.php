@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto p-4">
  <div class="flex items-center justify-between mb-3">
    <h1 class="text-xl font-semibold">รายงานรายรับ–รายจ่าย</h1>
    <div class="flex gap-2">
      <a class="btn" href="{{ route('admin.reports.cashflow.csv', request()->only('from','to')) }}">ดาวน์โหลด CSV</a>
    </div>
  </div>

  <form method="get" class="flex flex-wrap gap-2 mb-4">
    <input type="date" name="from" value="{{ $from }}" class="input input-bordered">
    <input type="date" name="to"   value="{{ $to   }}" class="input input-bordered">
    <button class="btn">ดูรายงาน</button>
  </form>

  <div class="grid md:grid-cols-3 gap-3 mb-3">
    <div class="stat bg-base-100 shadow"><div class="stat-title">รายรับ</div><div class="stat-value text-success">{{ number_format($incomeTotal,2) }}</div></div>
    <div class="stat bg-base-100 shadow"><div class="stat-title">รายจ่าย</div><div class="stat-value text-error">{{ number_format($expenseTotal,2) }}</div></div>
    <div class="stat bg-base-100 shadow"><div class="stat-title">กำไร/ขาดทุนสุทธิ</div><div class="stat-value">{{ number_format($netTotal,2) }}</div></div>
  </div>

  <div class="card bg-base-100 shadow">
    <div class="overflow-x-auto">
      <table class="table">
        <thead><tr><th>วันที่</th><th class="text-right">รายรับ</th><th class="text-right">รายจ่าย</th><th class="text-right">สุทธิ</th></tr></thead>
        <tbody>
          @foreach($rows as $r)
            <tr>
              <td>{{ \Carbon\Carbon::parse($r['date'])->format('d/m/Y') }}</td>
              <td class="text-right">{{ number_format($r['income'],2) }}</td>
              <td class="text-right">{{ number_format($r['expense'],2) }}</td>
              <td class="text-right">{{ number_format($r['net'],2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
