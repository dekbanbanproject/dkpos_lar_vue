@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto p-4">
  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <h1 class="text-2xl font-semibold">สั่งซื้อสำเร็จ</h1>
      <p class="opacity-70">เลขที่ออเดอร์: <b>{{ $order->order_no }}</b></p>

      <div class="divider"></div>

      <div class="overflow-x-auto">
        <table class="table">
          <thead><tr><th>สินค้า</th><th class="text-right">ราคา</th><th class="text-right">จำนวน</th><th class="text-right">รวม</th></tr></thead>
          <tbody>
            @foreach($order->items as $it)
              <tr>
                <td>{{ $it->name }}</td>
                <td class="text-right">{{ number_format($it->price,2) }}</td>
                <td class="text-right">{{ $it->qty }}</td>
                <td class="text-right">{{ number_format($it->total,2) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="divider"></div>

      <div class="flex justify-end">
        <div class="text-right">
          <div>รวม: <b>{{ number_format($order->sub_total,2) }}</b></div>
          <div>สุทธิ: <b>{{ number_format($order->total,2) }}</b></div>
        </div>
      </div>

      <div class="mt-4">
        <a href="{{ route('shop.home') }}" class="btn btn-primary">กลับหน้าร้าน</a>
      </div>
    </div>
  </div>
</div>

{{-- เคลียร์ตะกร้าหน้าสำเร็จ --}}
@push('scripts')
<script>localStorage.removeItem('shop_cart');</script>
@endpush
@endsection
