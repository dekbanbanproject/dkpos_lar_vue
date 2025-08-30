@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-4">
  <h1 class="text-xl font-semibold mb-3">รายการรอตรวจสลิป / ชำระเงิน</h1>

  @if(session('ok')) <div class="alert alert-success mb-3">{{ session('ok') }}</div> @endif

  {{-- ฟิลเตอร์ --}}
  <form method="GET" class="card bg-base-100 shadow mb-3">
    <div class="card-body grid md:grid-cols-4 gap-3 items-end">
      <div>
        <label class="label">สถานะ</label>
        <select name="status" class="select select-bordered w-full">
          @php
            $opts = [
              'awaiting_payment,pending' => 'รอชำระ/รอตรวจสลิป (ค่าเริ่มต้น)',
              'paid' => 'ชำระแล้ว',
              'cancelled' => 'ยกเลิก',
              'all' => 'แสดงทั้งหมด',
            ];
          @endphp
          @foreach($opts as $val => $text)
            <option value="{{ $val }}" @selected(($status ?? '') === $val)>{{ $text }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="label">ค้นหา (เลขที่ออเดอร์/ชื่อลูกค้า/เบอร์)</label>
        <input type="text" name="q" value="{{ $search ?? '' }}" class="input input-bordered w-full" placeholder="พิมพ์แล้วกด Enter">
      </div>
      <div>
        <button class="btn btn-primary w-full">ค้นหา</button>
      </div>
    </div>
  </form>

  <div class="card bg-base-100 shadow">
    <div class="card-body overflow-x-auto p-0">
      <table class="table">
        <thead>
          <tr>
            <th>วันที่</th>
            <th>เลขที่</th>
            <th>ลูกค้า</th>
            <th class="text-right">ยอด</th>
            <th>สถานะ</th>
            <th>สลิป</th>
            <th class="text-right">จัดการ</th>
          </tr>
        </thead>
        <tbody>
        @forelse($orders as $o)
          <tr>
            <td>{{ $o->created_at->format('Y-m-d H:i') }}</td>
            <td>{{ $o->order_no }}</td>
            <td>
              {{ $o->customer_name ?? '-' }}
              <div class="text-xs opacity-70">{{ $o->customer_phone }}</div>
            </td>
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
              <div class="flex justify-end gap-2">
                <a href="{{ route('admin.orders.edit',$o) }}" class="btn btn-sm">แก้ไข</a>
                @if(in_array($o->status,['awaiting_payment','pending']))
                  <form method="POST" action="{{ route('admin.orders.approve',$o) }}">
                    @csrf
                    <button class="btn btn-sm btn-primary">อนุมัติ</button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center py-10 opacity-60">ไม่พบรายการ</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    <div class="p-3">{{ $orders->links() }}</div>
  </div>
</div>
@endsection
