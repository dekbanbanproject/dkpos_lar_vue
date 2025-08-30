@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-4">
  <div class="flex items-center justify-between mb-3">
    <h1 class="text-xl font-semibold">รับสินค้าเข้าคลัง</h1>
    <a class="btn btn-primary" href="{{ route('admin.stock-ins.create') }}">+ สร้างเอกสารรับ</a>
  </div>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

  <div class="card bg-base-100 shadow">
    <div class="overflow-x-auto">
      <table class="table">
        <thead>
          <tr>
            <th>เลขที่</th><th>วันที่รับ</th><th>ผู้ทำรายการ</th><th>จำนวนรวม</th><th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($docs as $d)
          <tr>
            <td>{{ $d->ref_no }}</td>
            <td>{{ \Carbon\Carbon::parse($d->received_at)->format('d/m/Y H:i') }}</td>
            <td>{{ $d->user?->name ?? '-' }}</td>
            <td>{{ $d->total_qty }}</td>
            <td><a class="btn btn-xs" href="{{ route('admin.stock-ins.show', $d->id) }}">ดู</a></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="p-3">{{ $docs->links() }}</div>
  </div>
</div>
@endsection
