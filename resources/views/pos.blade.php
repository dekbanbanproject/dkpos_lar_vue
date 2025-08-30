<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bakery POS</title>
@vite(['resources/css/app.css','resources/js/pos.js'])
</head>
<body class="bg-base-200 min-h-screen">
  <div class="navbar bg-base-100 shadow">
    <div class="flex-1 px-4 text-xl font-bold">🍞 Bakery POS</div>

    @auth('web')
      <ul class="menu menu-horizontal px-2">
        {{-- เห็นทุกสิทธิ์ --}}
        <li><a href="{{ route('pos') }}">POS</a></li>

        {{-- admin + manager --}}
        @if(in_array(auth('web')->user()->role, ['admin','manager']))
          <li><a href="{{ route('products.index') }}">สินค้า</a></li>
          <li><a href="{{ route('reports.index') }}">รายงาน</a></li>
        @endif

        {{-- admin เท่านั้น --}}
        @if(auth('web')->user()->role === 'admin')
          <li><a href="{{ route('admin.users') }}">ผู้ใช้</a></li>
        @endif
      </ul>

      <div class="px-2 text-sm opacity-70">คุณ: {{ auth('web')->user()->name }} ({{ auth('web')->user()->role }})</div>
      <form method="post" action="{{ route('logout') }}" class="px-2">@csrf
        <button class="btn btn-sm">ออกจากระบบ</button>
      </form>
    @endauth
  </div>

  {{-- ส่ง role ไปให้ Vue ใช้ด้วย --}}
  <script>window.userRole = "{{ auth('web')->user()->role ?? '' }}";</script>

  <main class="container mx-auto p-4">
    <div id="app"></div>
  </main>
</body>

</html>