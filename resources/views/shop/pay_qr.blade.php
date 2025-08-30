@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-4" x-data="payPage('{{ $order->id }}')">
  <h1 class="text-2xl font-semibold">ชำระเงินคำสั่งซื้อ</h1>
  <div class="opacity-70">เลขที่ออเดอร์: <b>{{ $order->order_no }}</b> | ยอดสุทธิ: <b>{{ number_format($order->total,2) }} บาท</b></div>

  <div class="grid md:grid-cols-2 gap-4 mt-4">
    <div class="card bg-base-100 shadow">
      <div class="card-body items-center">
        <h2 class="card-title">สแกนจ่ายด้วย PromptPay</h2>
        <div class="mt-2">{!! $qrSvg !!}</div>
        <div class="mt-2 text-xs opacity-70 break-all">Payload: {{ $payload }}</div>
      </div>
    </div>

    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <h2 class="card-title">อัปโหลดสลิปโอน</h2>

        @if (session('ok'))
          <div class="alert alert-success">{{ session('ok') }}</div>
        @endif

        <form method="POST" action="{{ route('shop.checkout.slip',$order) }}" enctype="multipart/form-data">
          @csrf
          <div class="form-control">
            <label class="label">จำนวนเงิน (ถ้ามี)</label>
            <input type="number" step="0.01" name="amount" class="input input-bordered">
          </div>
          <div class="form-control mt-2">
            <label class="label">สลิป (JPG/PNG ≤ 5MB)</label>
            <input type="file" name="slip" class="file-input file-input-bordered" required>
          </div>
          <button class="btn btn-primary mt-3">ส่งสลิป</button>
        </form>

        @error('slip')<div class="text-error mt-2">{{ $message }}</div>@enderror
      </div>
    </div>
  </div>

  <div class="mt-6 text-center">
    <div class="opacity-70">สถานะปัจจุบัน: <b x-text="status"></b></div>
    <button class="btn btn-sm mt-2" @click="check()">ตรวจสอบอีกครั้ง</button>
  </div>
</div>

@push('scripts')
<script>
  function payPage(orderId) {
    return {
      status: 'loading...',
      async check(){
        try{
          const res = await fetch(`{{ url('/shop/api/orders') }}/${orderId}/status`);
          const json = await res.json();
          this.status = json.status;
          if (json.status === 'paid') {
            window.location.href = `{{ url('/checkout/done') }}/${orderId}`;
          }
        }catch(e){ this.status = 'error'; }
      },
      init(){
        this.check();
        setInterval(()=>this.check(), 5000);
      }
    }
  }
</script>
@endpush
@endsection
