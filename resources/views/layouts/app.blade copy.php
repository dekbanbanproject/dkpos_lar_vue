<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Bakery POS</title>

  @vite(['resources/css/app.css','resources/js/app.js'])
    
  @stack('styles')
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>
<body class="bg-base-200 min-h-screen">
  {{-- <nav class="navbar bg-base-100 shadow"> --}}
    <nav class="navbar bg-base-100/95 backdrop-blur shadow sticky top-0 z-50">
    <div class="flex-1">
      <a href="{{ route('pos') }}" class="btn btn-ghost normal-case text-xl">Bakery POS</a>
    </div>
    <div class="flex-none gap-2">
      @auth('web')
        @php $role = auth('web')->user()->role ?? null; @endphp

        @if(in_array($role, ['admin','manager']))
            <a href="{{ route('admin.products.index') }}" class="btn btn-ghost btn-sm">สินค้า</a>
            <a href="{{ route('admin.stock-ins.index') }}" class="btn btn-ghost btn-sm">รับสินค้าเข้า</a>
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-ghost btn-sm">รายจ่าย</a>
            <a href="{{ route('admin.reports.cashflow') }}" class="btn btn-ghost btn-sm">รายงาน</a>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="inline">
          @csrf
          <button class="btn btn-sm">ออกจากระบบ</button>
        </form>
      @endauth
    </div>
  </nav>

  <main class="container mx-auto p-4">
    @yield('content')
  </main>
  
  @stack('scripts')

</body>
</html>
