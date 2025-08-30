{{-- resources/views/customer/dashboard.blade.php --}}
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Customer</title>
  @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen">
  <div class="navbar bg-base-100 shadow">
    <div class="flex-1 px-4 font-bold">🍞 Bakery — Customer</div>
    <form method="post" action="{{ route('customer.logout') }}" class="px-4">
      @csrf
      <button class="btn btn-sm">ออกจากระบบ</button>
    </form>
  </div>
  <main class="container mx-auto p-4">
    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <h2 class="card-title">สวัสดี, {{ auth('customer')->user()->name }}</h2>
        <p class="opacity-70">หน้านี้ไว้แสดงประวัติการสั่งซื้อ/โปรไฟล์ (ต่อยอดภายหลัง)</p>
      </div>
    </div>
  </main>
</body>
</html>
