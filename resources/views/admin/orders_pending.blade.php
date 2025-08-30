@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto p-4">
  <h1 class="text-xl font-semibold mb-4">รายการรอตรวจสลิป / ชำระเงิน</h1>

  @if(session('ok'))<div class="alert alert-success">{{ session('ok') }}</div>@endif

  <div class="overflow-x-auto card bg-base-100 shadow">
    <table class="table">
      <thead><tr><th>วันที่</th><th>เลขที่</th><th>ลูกค้า</th><th>ยอด</th><th>สถานะ</th><th>สลิป</th><th></th></tr></thead>
      <tbody>
      @foreach($orders as $o)
        <tr>
          <td>{{ $o->created_at->format('Y-m-d H:i') }}</td>
          <td>{{ $o->order_no }}</td>
          <td>{{ $o->customer_name }}<div class="text-xs opacity-70">{{ $o->customer_phone }}</div></td>
          <td class="text-right">{{ number_format($o->total,2) }}</td>
          <td>{{ $o->status }}</td>
          <td>
            @if($o->payment_slip_path)
              <a href="{{ Storage::url($o->payment_slip_path) }}" target="_blank" class="link link-primary">ดูสลิป</a>
            @else
              <span class="opacity-50">-</span>
            @endif
          </td>
          <td class="text-right">
            <form method="POST" action="{{ route('admin.orders.approve',$o) }}">
              @csrf
              <button class="btn btn-sm btn-primary">อนุมัติเป็นชำระแล้ว</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
