@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-4" x-data="checkoutPage()">
  <h1 class="text-2xl font-semibold mb-4">ชำระเงิน</h1>

  <div class="grid lg:grid-cols-3 gap-4">
    <!-- ฟอร์มลูกค้า -->
    <div class="lg:col-span-2">
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <form method="POST" action="{{ route('shop.checkout.store') }}" @submit="sync()">
            @csrf
            <div class="grid md:grid-cols-2 gap-3">
              <div>
                <label class="label">ชื่อผู้สั่งซื้อ</label>
                <input name="customer_name" class="input input-bordered w-full" required>
              </div>
              <div>
                <label class="label">เบอร์โทร</label>
                <input name="customer_phone" class="input input-bordered w-full" required>
              </div>
              <div class="md:col-span-2">
                <label class="label">ที่อยู่จัดส่ง (ถ้ามี)</label>
                <textarea name="customer_address" class="textarea textarea-bordered w-full" rows="3"></textarea>
              </div>
              <div class="md:col-span-2">
                <label class="label">วิธีชำระ</label>
                <select name="payment_method" class="select select-bordered w-full" x-model="payment" required>
                  <option value="cod">เก็บเงินปลายทาง</option>
                  <option value="transfer">โอนเงิน</option>
                  <option value="qr">QR</option>
                  <option value="card">บัตร</option>
                </select>
              </div>
            </div>

            <!-- ส่งตะกร้าไปหลังบ้าน -->
            <input type="hidden" name="cart_json" :value="cartJson">

            <div class="mt-4 flex justify-end gap-2">
              <a href="{{ route('shop.home') }}" class="btn btn-ghost">กลับไปเลือกสินค้า</a>
              <button class="btn btn-primary" :disabled="cart.length===0">ยืนยันสั่งซื้อ</button>
            </div>
          </form>

          @if ($errors->any())
            <div class="alert alert-error mt-3">
              <ul class="list-disc pl-6">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
              </ul>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- สรุปตะกร้า -->
    <aside>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h2 class="card-title">ตะกร้าสินค้า</h2>
          <template x-if="cart.length===0">
            <div class="opacity-70">ยังไม่มีสินค้าในตะกร้า</div>
          </template>

          <div class="flex flex-col gap-2 max-h-[50vh] overflow-auto pr-1" x-show="cart.length">
            <template x-for="(c, i) in cart" :key="c.id">
              <div class="flex items-center justify-between gap-2 bg-base-200 rounded-xl p-2">
                <div class="flex items-center gap-2">
                  <img :src="thumb(c)" class="w-12 h-12 object-cover rounded">
                  <div>
                    <div class="font-semibold leading-tight" x-text="c.name"></div>
                    <div class="text-xs opacity-70" x-text="money(c.price)"></div>
                  </div>
                </div>
                <div class="flex items-center gap-1">
                  <button class="btn btn-xs" @click="dec(i)">-</button>
                  <input type="number" class="input input-bordered input-xs w-14 text-center" min="1" x-model.number="c.qty" @change="save()">
                  <button class="btn btn-xs" @click="inc(i)">+</button>
                </div>
                <div class="text-right w-24 font-medium" x-text="money(c.price*c.qty)"></div>
                <button class="btn btn-error btn-xs" @click="remove(i)">ลบ</button>
              </div>
            </template>
          </div>

          <div class="divider"></div>
          <div class="space-y-1">
            <div class="flex justify-between"><span>รวม</span><span class="font-semibold" x-text="money(subTotal)"></span></div>
            <div class="flex justify-between text-lg"><span>สุทธิ</span><span class="font-bold" x-text="money(subTotal)"></span></div>
          </div>
        </div>
      </div>
    </aside>
  </div>
</div>

<script>
  window.checkoutPage = () => ({
    cart: JSON.parse(localStorage.getItem('shop_cart') || '[]'),
    cartJson: '[]',
    payment: 'cod',
    money(n){ return new Intl.NumberFormat('th-TH',{style:'currency',currency:'THB'}).format(n ?? 0) },
    thumb(p){ return p.image_path || 'https://placehold.co/600x600?text=Bakery' },
    get subTotal(){ return this.cart.reduce((s,i)=> s + i.price*i.qty, 0) },
    inc(i){ this.cart[i].qty++; this.save() },
    dec(i){ this.cart[i].qty = Math.max(1, this.cart[i].qty-1); this.save() },
    remove(i){ this.cart.splice(i,1); this.save() },
    save(){ localStorage.setItem('shop_cart', JSON.stringify(this.cart)); this.sync() },
    sync(){ this.cartJson = JSON.stringify(this.cart) },
    init(){ this.sync() }
  })
</script>
@endsection
