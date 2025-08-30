@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto p-4">
  <h1 class="text-xl font-semibold mb-3">ใบรับสินค้า {{ $doc->ref_no }}</h1>

  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <div class="grid md:grid-cols-2 gap-2 text-sm">
        <div>วันที่รับ: <strong>{{ \Carbon\Carbon::parse($doc->received_at)->format('d/m/Y H:i') }}</strong></div>
        <div>ผู้ทำรายการ: <strong>{{ $doc->user?->name ?? '-' }}</strong></div>
        <div>ผู้จำหน่าย: <strong>{{ $doc->supplier_name ?? '-' }}</strong></div>
        <div>หมายเหตุ: <strong>{{ $doc->note ?? '-' }}</strong></div>
      </div>

      <div class="overflow-x-auto mt-3">
        <table class="table">
          <thead><tr><th>#</th><th>สินค้า</th><th class="text-right">จำนวน</th><th class="text-right">ต้นทุน</th></tr></thead>
          <tbody>
            @foreach($doc->items as $i => $it)
              <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $it->product?->name ?? '-' }}</td>
                <td class="text-right">{{ number_format($it->qty) }}</td>
                <td class="text-right">{{ $it->cost !== null ? number_format($it->cost,2) : '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="text-right mt-3">
        รวมจำนวนทั้งหมด: <strong>{{ $doc->total_qty }}</strong>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <a href="{{ route('admin.stock-ins.index') }}" class="btn">กลับ</a>
  </div>
</div>
@endsection
