@extends('layouts.app')

@push('styles')
  {{-- Select2 (ค้นหาสินค้าใน <select>) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <style>
    .select2-container{width:100%!important}
    .select2-container .select2-selection--single{height:2.5rem;padding:0 .75rem}
    .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:2.5rem}
    .select2-container--default .select2-selection--single .select2-selection__arrow{height:2.5rem;right:.5rem}
  </style>
@endpush

@section('content')
@php
  // เตรียมข้อมูลเริ่มต้นสำหรับ Alpine
  $initItems = $order->items->map(fn($i)=>[
    'product_id' => $i->product_id,
    'price'      => (float)$i->price,
    'qty'        => (int)$i->qty,
  ])->values();

  $allProducts = $products->map(fn($p)=>[
    'id'    => $p->id,
    'name'  => $p->name,
    'price' => (float)$p->price,
    'stock' => (int)$p->stock,
  ])->values();
@endphp

<div class="max-w-6xl mx-auto p-4"
     x-data="orderEditForm({
        items: @js($initItems),
        products: @js($allProducts),
        discount: {{ (float)($order->discount ?? 0) }}
      })"
     x-init="init()">

  <div class="mb-4">
    <h1 class="text-2xl font-semibold">แก้ไขออเดอร์</h1>
    <div class="text-sm opacity-70">
      เลขที่ <b>{{ $order->order_no }}</b> |
      สถานะ <b>{{ $order->status }}</b> |
      วันที่ <b>{{ $order->created_at->format('Y-m-d H:i') }}</b>
    </div>
  </div>

  @if ($errors->any())
    <div class="alert alert-error mb-3">
      <ul class="list-disc pl-6">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif
  @if (session('ok'))
    <div class="alert alert-success mb-3">{{ session('ok') }}</div>
  @endif

  <form method="POST" action="{{ route('admin.orders.update', $order) }}" @submit="beforeSubmit">
    @csrf
    @method('PUT')

    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <div class="flex items-center justify-between mb-2">
          <div class="font-semibold">รายการสินค้า</div>
          <div class="flex items-center gap-2">
            <button type="button" class="btn btn-sm" @click="addRow()">+ เพิ่มแถว</button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th style="min-width: 16rem">สินค้า</th>
                <th class="w-36 text-right">ราคา</th>
                <th class="w-28 text-right">จำนวน</th>
                <th class="w-36 text-right">ยอดรวม</th>
                <th class="w-10"></th>
              </tr>
            </thead>
            <tbody>
              <template x-for="(row, i) in rows" :key="row.key">
                <tr>
                  <td>
                    <select class="js-product-select select select-bordered w-full"
                            :name="`items[${i}][product_id]`"
                            x-model.number="row.product_id"
                            @change="onProductChange(i)"
                            required>
                      <option value="" disabled>-- เลือกสินค้า --</option>
                      @foreach($products as $p)
                        <option value="{{ $p->id }}">
                          {{ $p->name }} (คงเหลือ {{ $p->stock }})
                        </option>
                      @endforeach
                    </select>
                    <div class="text-xs opacity-70 mt-1" x-show="row.product_id">
                      <template x-if="product(row.product_id)">
                        <span>
                          สต็อก: <b x-text="product(row.product_id).stock"></b>
                          | ราคาแนะนำ: <b x-text="money(product(row.product_id).price)"></b>
                        </span>
                      </template>
                    </div>
                  </td>
                  <td>
                    <input type="number" step="0.01" min="0"
                           class="input input-bordered w-full text-right"
                           :name="`items[${i}][price]`"
                           x-model.number="row.price" required>
                  </td>
                  <td>
                    <input type="number" min="1"
                           class="input input-bordered w-full text-right"
                           :name="`items[${i}][qty]`"
                           x-model.number="row.qty" required>
                  </td>
                  <td class="text-right align-middle font-medium">
                    <span x-text="money(lineTotal(row))"></span>
                  </td>
                  <td>
                    <button type="button" class="btn btn-error btn-xs" @click="removeRow(i)">ลบ</button>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <div class="mt-3 grid md:grid-cols-2 gap-3">
          <div>
            <label class="label">เหตุผลในการแก้ไข (แนะนำให้ระบุ)</label>
            <textarea name="reason" class="textarea textarea-bordered w-full" rows="3"
                      placeholder="เช่น ใส่จำนวนผิด / ลูกค้าขอเปลี่ยนรายการ"></textarea>
          </div>

          <div class="md:text-right space-y-2">
            <div>รวม (ก่อนส่วนลด): <b x-text="money(subTotal)"></b></div>
            <div class="flex items-center justify-end gap-2">
              <span>ส่วนลด</span>
              <input type="number" step="0.01" min="0"
                     class="input input-bordered w-40 text-right"
                     name="discount"
                     x-model.number="discount">
            </div>
            <div class="text-lg">สุทธิ: <b x-text="money(total)"></b></div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-4 flex justify-end gap-2">
      <a href="{{ route('admin.orders.pending') }}" class="btn btn-ghost">ยกเลิก</a>
      <button class="btn btn-primary" :disabled="rows.length===0">บันทึกการแก้ไข</button>
    </div>
  </form>
</div>

{{-- Alpine Component --}}
<script>
  // init Select2 ทุกครั้งที่ DOM พร้อม และหลังเพิ่มแถว
  window.initSelects = function(){
    document.querySelectorAll('.js-product-select').forEach(el=>{
      if (!window.jQuery) return; // ถ้ายังไม่ได้โหลด jQuery ก็ข้าม
      if (!$(el).data('select2')) {
        $(el).select2({ width:'100%', placeholder:'พิมพ์เพื่อค้นหา…', allowClear:true });
      }
    });
  };

  window.orderEditForm = (state) => ({
    products: state.products || [],
    rows: [],
    discount: state.discount ?? 0,

    init(){
      // แปลงรายการเริ่มต้น
      this.rows = (state.items || []).map(it => ({
        key: Date.now() + Math.random(),
        product_id: it.product_id || '',
        price: Number(it.price ?? 0),
        qty: Number(it.qty ?? 1),
      }));
      this.$nextTick(() => window.initSelects && window.initSelects());
    },

    product(id){ return this.products.find(p => p.id == id) },
    addRow(){
      this.rows.push({ key: Date.now()+Math.random(), product_id:'', price:0, qty:1 });
      this.$nextTick(() => window.initSelects && window.initSelects());
    },
    removeRow(i){ this.rows.splice(i,1) },

    onProductChange(i){
      const row = this.rows[i];
      const p = this.product(row.product_id);
      if (p && (!row.price || row.price === 0)) row.price = Number(p.price || 0);
    },

    lineTotal(row){ return Math.max(0, Number(row.price||0) * Number(row.qty||0)) },
    get subTotal(){ return this.rows.reduce((s,r)=> s + this.lineTotal(r), 0) },
    get total(){ return Math.max(0, this.subTotal - Number(this.discount||0)) },

    money(n){ return new Intl.NumberFormat('th-TH',{style:'currency',currency:'THB'}).format(n ?? 0) },

    beforeSubmit(){
      // กันส่งฟอร์มถ้าไม่มีรายการ
      if (this.rows.length === 0) {
        alert('กรุณาใส่รายการอย่างน้อย 1 รายการ'); event.preventDefault();
      }
    }
  });
</script>
@endsection

@push('scripts')
  {{-- jQuery + Select2 --}}
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>document.addEventListener('DOMContentLoaded', ()=> setTimeout(window.initSelects, 0));</script>
@endpush
