@extends('layouts.app')

@section('content')
  {{-- ส่ง role ไปให้ Vue --}}
  <script>window.userRole = "{{ $role ?? 'cashier' }}";</script>

  {{-- Vue จะ mount ตรงนี้ --}}
  <div id="app"></div>
@endsection
