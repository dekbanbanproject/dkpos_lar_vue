<template>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- ซ้าย: สินค้า -->
    <section class="lg:col-span-2">
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <div class="flex flex-col md:flex-row gap-2 items-center">
            <input
              v-model="search"
              @input="debouncedFetch"
              type="text"
              placeholder="ค้นหาสินค้า / สแกนบาร์โค้ด..."
              class="input input-bordered w-full"
            />
            <button class="btn" @click="fetchProducts">ค้นหา</button>
          </div>

          <div class="mt-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
            <div
              v-for="p in products"
              :key="p.id"
              class="card bg-base-200 hover:bg-base-300 cursor-pointer"
              @click="addToCart(p)"
            >
              <figure class="aspect-square overflow-hidden">
                <img :src="thumb(p)" class="object-cover w-full h-full" alt="">
              </figure>
              <div class="card-body p-3">
                <div class="font-semibold leading-tight">{{ p.name }}</div>
                <div class="text-sm opacity-70">คงเหลือ: {{ p.stock }}</div>
                <div class="text-right text-lg font-bold">{{ money(p.price) }}</div>
              </div>
            </div>
          </div>

          <div v-if="!loading && products.length === 0" class="text-center py-10 opacity-70">ไม่พบสินค้า</div>
          <div v-if="loading" class="text-center py-10">
            <span class="loading loading-spinner loading-lg"></span>
          </div>
        </div>
      </div>
    </section>

    <!-- ขวา: ตะกร้า -->
    <aside>
      <div class="card bg-base-100 shadow-xl sticky top-4">
        <div class="card-body">
          <h2 class="card-title">ตะกร้าสินค้า</h2>

          <div v-if="cart.length === 0" class="opacity-70">ยังไม่มีสินค้าในตะกร้า</div>

          <div v-else class="flex flex-col gap-2 max-h-[50vh] overflow-auto pr-1">
            <div
              v-for="(c,idx) in cart"
              :key="c.id"
              class="flex items-center justify-between gap-2 bg-base-200 rounded-xl p-2"
            >
              <div class="flex-1">
                <div class="font-semibold leading-tight">{{ c.name }}</div>
                <div class="text-sm opacity-70">
                  {{ money(c.price) }} x {{ c.qty }} = {{ money(c.price*c.qty) }}
                </div>
              </div>
              <div class="flex items-center gap-1">
                <button class="btn btn-xs" @click="decrement(idx)">-</button>
                <input
                  type="number"
                  class="input input-bordered input-xs w-16 text-center"
                  v-model.number="c.qty"
                  min="1"
                />
                <button class="btn btn-xs" @click="increment(idx)">+</button>
              </div>
              <button class="btn btn-error btn-xs" @click="remove(idx)">ลบ</button>
            </div>
          </div>

          <div class="divider"></div>

          <div class="space-y-2">
            <div class="flex justify-between">
              <span>ยอดรวม</span>
              <span class="font-semibold">{{ money(subTotal) }}</span>
            </div>
            <div class="flex items-center justify-between gap-2">
              <span>ส่วนลด</span>
              <input
                type="number"
                class="input input-bordered input-sm w-40 text-right"
                v-model.number="discount"
                min="0"
              />
            </div>
            <div class="flex justify-between text-lg">
              <span>สุทธิ</span>
              <span class="font-bold">{{ money(total) }}</span>
            </div>
            <div class="flex items-center justify-between gap-2">
              <span>รับเงิน</span>
              <input
                type="number"
                class="input input-bordered input-sm w-40 text-right"
                v-model.number="paid"
                min="0"
              />
            </div>
            <div class="flex justify-between">
              <span>ทอน</span>
              <span class="font-semibold">{{ money(change) }}</span>
            </div>
          </div>

          <div class="form-control mt-2">
            <label class="label"><span class="label-text">วิธีชำระ</span></label>
            <select v-model="payment_method" class="select select-bordered">
              <option value="cash">เงินสด</option>
              <option value="qr">QR</option>
              <option value="card">บัตร</option>
            </select>
          </div>

          <div class="card-actions mt-4 flex gap-2">
            <button class="btn btn-ghost" @click="clearCart" :disabled="cart.length===0">ล้างตะกร้า</button>
            <button
              class="btn btn-primary flex-1"
              @click="checkout"
              :disabled="cart.length===0 || total<=0 || paid<total"
            >ชำระเงิน</button>
          </div>

          <p v-if="error" class="text-error mt-2">{{ error }}</p>
          <p v-if="success" class="text-success mt-2">{{ success }}</p>
        </div>
      </div>

      <!-- ใบเสร็จ -->
      <dialog ref="receiptDialog" class="modal">
        <div class="modal-box">
          <h3 class="font-bold text-lg">ใบเสร็จ {{ receipt.order_no }}</h3>
          <div class="py-2 text-sm opacity-70">พิมพ์ได้ทันทีจากเบราว์เซอร์</div>
          <div class="divider"></div>
          <div class="space-y-1 max-h-72 overflow-auto">
            <div class="flex justify-between" v-for="it in receipt.items" :key="it.id">
              <span>{{ it.name }} x {{ it.qty }}</span>
              <span>{{ money(it.total) }}</span>
            </div>
          </div>
          <div class="divider"></div>
          <div class="space-y-1">
            <div class="flex justify-between"><span>รวม</span><span>{{ money(receipt.sub_total) }}</span></div>
            <div class="flex justify-between"><span>ส่วนลด</span><span>{{ money(receipt.discount) }}</span></div>
            <div class="flex justify-between font-bold"><span>สุทธิ</span><span>{{ money(receipt.total) }}</span></div>
            <div class="flex justify-between"><span>รับเงิน</span><span>{{ money(receipt.paid) }}</span></div>
            <div class="flex justify-between"><span>ทอน</span><span>{{ money(receipt.change) }}</span></div>
          </div>
          <div class="modal-action">
            <button class="btn" @click="printReceipt">พิมพ์</button>
            <form method="dialog">
              <button class="btn">ปิด</button>
            </form>
          </div>
        </div>
      </dialog>
    </aside>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const products = ref([])
const loading = ref(false)
const search = ref('')
let timer = null

const cart = ref([])
const discount = ref(0)
const paid = ref(0)
const payment_method = ref('cash')

const error = ref('')
const success = ref('')

const receiptDialog = ref(null)
const receipt = ref({ order_no: '', items: [], sub_total: 0, discount: 0, total: 0, paid: 0, change: 0 })

const money = (n) => new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB' }).format(n ?? 0)
const thumb = (p) => p.image_path || 'https://placehold.co/600x600?text=Bakery'

const fetchProducts = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await axios.get('/api/products', { params: { search: search.value } })
    products.value = data.products || []
  } catch (e) {
    error.value = 'โหลดรายการสินค้าไม่สำเร็จ'
  } finally {
    loading.value = false
  }
}

const debouncedFetch = () => {
  clearTimeout(timer)
  timer = setTimeout(fetchProducts, 300)
}

onMounted(fetchProducts)

const addToCart = (p) => {
  const idx = cart.value.findIndex(i => i.id === p.id)
  if (idx >= 0) {
    if (cart.value[idx].qty < p.stock) cart.value[idx].qty++
  } else {
    if (p.stock > 0) cart.value.push({ id: p.id, name: p.name, price: p.price, qty: 1 })
  }
}

const increment = (i) => {
  const c = cart.value[i]
  const p = products.value.find(x => x.id === c.id)
  if (!p) return
  if (c.qty < p.stock) c.qty++
}
const decrement = (i) => { if (cart.value[i].qty > 1) cart.value[i].qty-- }
const remove = (i) => cart.value.splice(i, 1)
const clearCart = () => { cart.value = []; discount.value = 0; paid.value = 0; success.value = ''; error.value = '' }

const subTotal = computed(() => cart.value.reduce((s, c) => s + c.price * c.qty, 0))
const total   = computed(() => Math.max(0, subTotal.value - (discount.value || 0)))
const change  = computed(() => Math.max(0, (paid.value || 0) - total.value))

async function checkout () {
  error.value = ''; success.value = ''
  try {
    const payload = {
      items: cart.value.map(c => ({ product_id: c.id, qty: c.qty })),
      discount: discount.value || 0,
      paid: paid.value || 0,
      payment_method: payment_method.value,
    }
    const { data } = await axios.post('/api/orders', payload)
    success.value = data.message || 'สำเร็จ'
    const o = data.order || {}
    receipt.value = { ...o, items: o.items || [] }
    receiptDialog.value?.showModal()
    await fetchProducts()
    clearCart()
  } catch (e) {
    error.value = e?.response?.data?.message || 'ไม่สามารถชำระเงินได้'
  }
}

function printReceipt () { window.print() }
</script>
