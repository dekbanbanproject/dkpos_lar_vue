<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>แก้ไขสินค้า</title>
  @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 min-h-screen">
  <div class="navbar bg-base-100 shadow">
    <div class="flex-1 px-4 font-bold">แก้ไขสินค้า</div>
    <a href="{{ route('admin.products.index') }}" class="btn btn-sm mx-2">ย้อนกลับ</a>
  </div>

  <main class="container mx-auto p-4">
    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <form action="{{ route('admin.products.update', $product) }}" method="post" enctype="multipart/form-data">
          @method('PUT')
          @include('admin.products._form')
        </form>
      </div>
    </div>
  </main>
</body>
</html>
