{{-- resources/views/admin/products.blade.php --}}
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>จัดการสินค้า</title>
  @vite(['resources/css/app.css']) {{-- ใช้ Tailwind + DaisyUI --}}
</head>
<body class="bg-base-200 min-h-screen">
  <div class="navbar bg-base-100 shadow">
    <div class="flex-1 px-4 font-bold">🍞 Bakery — จัดการสินค้า</div>
    <a href="{{ route('pos') }}" class="btn btn-sm mx-2">ไปหน้า POS</a>
    <form action="{{ route('logout') }}" method="post" class="px-2">
      @csrf
      <button class="btn btn-sm">ออกจากระบบ</button>
    </form>
  </div>

  <main class="container mx-auto p-4">
    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <h2 class="card-title">รายการสินค้า</h2>
        <p class="opacity-70 text-sm">
          หน้านี้เข้าถึงได้เฉพาะ <b>admin</b> และ <b>manager</b>
        </p>

        {{-- ใส่ตาราง/ฟอร์มจัดการสินค้าในภายหลัง --}}
        <div class="alert">Placeholder สำหรับหน้า Admin Products</div>
      </div>
    </div>
  </main>
</body>
</html>
