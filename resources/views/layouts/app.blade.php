<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Bakery POS</title>

  {{-- โหลดทั้ง POS และ SHOP (อันไหนไม่มี element ก็จะไม่ mount เอง) --}}
  @vite(['resources/css/app.css','resources/js/app.js','resources/js/shop.js'])

  @stack('styles')
  {{-- ถ้าใช้ Alpine/Select2 ที่หน้าอื่น --}}
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-base-200 min-h-screen">
  <nav class="navbar bg-base-100/95 backdrop-blur shadow fixed top-0 inset-x-0 z-50">
    <div class="flex-1"><a href="{{ route('shop.home') }}" class="btn btn-ghost normal-case text-xl">Bakery POS</a></div>
    <div class="flex-none gap-2">
      @auth('web')
        @php $role = auth('web')->user()->role ?? null; @endphp
        @if(in_array($role, ['admin','manager']))
        <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm">หมวดหมู่</a>
          <a href="{{ route('admin.products.index') }}" class="btn btn-ghost btn-sm">สินค้า</a>
          <a href="{{ route('admin.stock-ins.index') }}" class="btn btn-ghost btn-sm">รับสินค้าเข้า</a>
          <a href="{{ route('admin.expenses.index') }}" class="btn btn-ghost btn-sm">รายจ่าย</a>
          <a href="{{ route('admin.reports.cashflow') }}" class="btn btn-ghost btn-sm">รายงาน</a>
         <a href="{{ route('admin.orders.pending') }}" class="btn btn-ghost btn-sm">ออเดอร์</a>

        @endif
        <form method="POST" action="{{ route('logout') }}" class="inline">@csrf<button class="btn btn-sm">ออกจากระบบ</button></form>
      @else
        <a href="{{ route('login') }}" class="btn btn-sm">เข้าสู่ระบบพนักงาน</a>
      @endauth
    </div>
  </nav>

  <main class="container mx-auto p-4 pt-20">
    @yield('content')
  </main>

  @stack('scripts')
</body>
</html>
