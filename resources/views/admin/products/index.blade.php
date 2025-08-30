<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>จัดการสินค้า</title>
  @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen">
  <div class="navbar bg-base-100 shadow">
    <div class="flex-1 px-4 font-bold">🍞 จัดการสินค้า</div>
    <a href="{{ route('pos') }}" class="btn btn-sm mx-2">ไปหน้า POS</a>
    <form method="post" action="{{ route('logout') }}" class="px-2">@csrf
      <button class="btn btn-sm">ออกจากระบบ</button>
    </form>
  </div>

  <main class="container mx-auto p-4">
    @if (session('ok')) <div class="alert alert-success mb-4">{{ session('ok') }}</div> @endif

    <div class="flex flex-col md:flex-row gap-2 justify-between items-center mb-3">
      <form method="get" class="flex gap-2 w-full md:w-auto">
        <input name="q" value="{{ $q }}" class="input input-bordered w-full md:w-72" placeholder="ค้นหาชื่อ/sku/บาร์โค้ด">
        <button class="btn">ค้นหา</button>
      </form>
      <a class="btn btn-primary" href="{{ route('admin.products.create') }}">+ เพิ่มสินค้า</a>
    </div>

    <div class="overflow-x-auto bg-base-100 rounded-xl shadow">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>รูป</th>
            <th>ชื่อ</th>
            <th>หมวด</th>
            <th class="text-right">ราคา</th>
            <th class="text-right">สต็อก</th>
            <th>สถานะ</th>
            <th class="text-right">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($products as $i => $p)
            <tr>
              <td>{{ $products->firstItem() + $i }}</td>
              <td>
                <div class="avatar">
                  <div class="w-14 rounded">
                    <img src="{{ $p->image_path ? asset($p->image_path) : 'https://placehold.co/80x80?text=Img' }}" alt="">
                  </div>
                </div>
              </td>
              <td>
                <div class="font-semibold">{{ $p->name }}</div>
                <div class="text-xs opacity-70">SKU: {{ $p->sku ?? '-' }} | BC: {{ $p->barcode ?? '-' }}</div>
              </td>
              <td>{{ $p->category->name ?? '-' }}</td>
              <td class="text-right">{{ number_format($p->price, 2) }}</td>
              <td class="text-right">{{ $p->stock }}</td>
              <td>
                @if($p->is_active) <span class="badge badge-success">ขาย</span>
                @else <span class="badge badge-ghost">ปิด</span> @endif
              </td>
              <td class="text-right">
                <a class="btn btn-sm" href="{{ route('admin.products.edit', $p) }}">แก้ไข</a>
                <form method="post" action="{{ route('admin.products.destroy', $p) }}" class="inline"
                      onsubmit="return confirm('ยืนยันลบสินค้า?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-error">ลบ</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-3">{{ $products->links() }}</div>
  </main>
</body>
</html>
