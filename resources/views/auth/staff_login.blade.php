{{-- resources/views/auth/staff_login.blade.php --}}
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Staff Login</title>
  @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
  <div class="card w-full max-w-sm bg-base-100 shadow-xl">
    <div class="card-body">
      <h2 class="card-title">เข้าสู่ระบบพนักงาน</h2>
      @if ($errors->any())
        <div class="alert alert-error text-sm">{{ $errors->first() }}</div>
      @endif
      <form method="post" action="{{ route('login') }}" class="space-y-3">
        @csrf
        <input class="input input-bordered w-full" name="login" placeholder="ชื่อผู้ใช้" value="{{ old('login') }}" required>
        <input class="input input-bordered w-full" type="password" name="password" placeholder="รหัสผ่าน" required>
        <button class="btn btn-primary w-full" type="submit">เข้าสู่ระบบ</button>
      </form>
      <div class="text-center text-sm opacity-70 mt-2">สำหรับพนักงานแคชเชียร์/ผู้จัดการ</div>
    </div>
  </div>
</body>
</html>
