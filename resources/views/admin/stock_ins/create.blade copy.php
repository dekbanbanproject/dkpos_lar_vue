@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-4" x-data="stockInForm()">
  <h1 class="text-xl font-semibold mb-3">สร้างเอกสารรับสินค้า</h1>

  <form method="POST" action="{{ route('admin.stock-ins.store') }}">
    @csrf

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="label">เลขที่เอกสาร</label>
        <input name="ref_no" class="input input-bordered w-full" value="{{ $ref }}" required>
      </div>
      <div>
        <label class="label">วันที่รับ</label>
        <input type="datetime-local" name="received_at" class="input input-bordered w-full"
               value="{{ now()->format('Y-m-d\TH:i') }}" required>
      </div>
      <div>
        <label class="label">ผู้จำหน่าย</label>
        <input name="supplier_name" class="input input-bordered w-full" placeholder="เช่น บริษัท A">
      </div>
      <div>
        <label class="label">หมายเหตุ</label>
        <input name="note" class="input input-bordered w-full">
      </div>
    </div>

    <div class="card bg-base-100 shadow mt-4">
      <div class="card-body">
        <div class="flex items-center justify-between mb-2">
          <div class="font-semibold">รายการสินค้า</div>
          <button type="button" class="btn btn-sm" @click="addRow()">+ เพิ่มแถว</button>
          
        </div>

        <div class="overflow-x-auto">
          <table class="table">
            <thead><tr><th>สินค้า</th><th class="w-36">จำนวน</th><th class="w-36">ต้นทุน/หน่วย</th><th class="w-12"></th></tr></thead>
            <tbody id="rows">
              <template x-for="(row, i) in rows" :key="row.key">
                <tr>
                  <td>
                    <select class="select select-bordered w-full"
                            :name="`items[${i}][product_id]`" x-model="row.product_id" required>
                      <option value="" disabled>-- เลือกสินค้า --</option>
                      @foreach($products as $p)
                        <option value="{{ $p->id }}">
                          {{ $p->name }} (คงเหลือ {{ $p->stock }})
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <input type="number" min="1" class="input input-bordered w-full text-right"
                           :name="`items[${i}][qty]`" x-model.number="row.qty" required>
                  </td>
                  <td>
                    <input type="number" step="0.01" min="0" class="input input-bordered w-full text-right"
                           :name="`items[${i}][cost]`" x-model.number="row.cost">
                  </td>
                  <td><button type="button" class="btn btn-error btn-xs" @click="removeRow(i)">ลบ</button></td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <div class="mt-3 text-right">
          รวมจำนวน: <span x-text="totalQty()"></span> หน่วย
        </div>
      </div>
    </div>

    <div class="mt-4 flex justify-end gap-2">
      <a href="{{ route('admin.stock-ins.index') }}" class="btn btn-ghost">ยกเลิก</a>
      <button class="btn btn-primary">บันทึก</button>
    </div>
  </form>
</div>

<script>
  // ✅ ประกาศบน window
  window.stockInForm = function () {
    return {
      rows: [{key: Date.now(), product_id: '', qty: 1, cost: null}],
      addRow(){ this.rows.push({key: Date.now()+Math.random(), product_id:'', qty:1, cost:null}) },
      removeRow(i){ this.rows.splice(i,1) },
      totalQty(){ return this.rows.reduce((s,r)=>s + (parseInt(r.qty)||0), 0) }
    }
  }
</script>

@endsection
