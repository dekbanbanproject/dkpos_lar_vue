<!-- resources/js/components/ShopHome.vue -->
<template>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
    <!-- ขวา: สินค้า + แบ่งหน้า + ตะกร้า (โชว์เฉพาะหมวด ID=5) -->
    <section class="lg:col-span-12">
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <h2 class="card-title">สินค้า (หมวด: เบเกอรี่)</h2>
            </div>
            <div class="flex items-center gap-2">
              <input v-model="search" @keydown.enter.prevent="reload"
                     type="text" placeholder="ค้นหาเฉพาะในหมวดเบเกอรี่…"
                     class="input input-bordered w-64" />
              <button class="btn" @click="reload">ค้นหา</button>
              <button class="btn btn-primary btn-sm" @click="openCart">
                ตะกร้า ({{ cartCount }})
              </button>
            </div>
          </div>

          <!-- กริด 6 การ์ดต่อแถว -->
          <div class="mt-4">
            <div v-if="products.length" class="grid gap-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
              <div v-for="p in products" :key="p.id" class="card bg-base-100 border">
                <figure class="aspect-square overflow-hidden">
                  <img :src="thumb(p)" class="w-full h-full object-cover" alt="">
                </figure>
                <div class="card-body p-3">
                  <div class="font-semibold leading-tight min-h-[2.6rem]">{{ p.name }}</div>
                  <div class="text-sm font-medium">{{ money(p.price) }}</div>
                  <div class="text-xs opacity-70">คงเหลือ: {{ p.stock }}</div>

                  <div class="mt-2 flex items-center justify-between gap-1 whitespace-nowrap">
                    <div class="flex items-center gap-1">
                      <button class="btn btn-xs" @click="decQty(p.id)">-</button>
                      <input type="number" class="input input-bordered input-xs w-12 text-center"
                             v-model.number="qty[p.id]" :min="1" :max="p.stock"
                             @keydown.enter.prevent="addToCart(p)" />
                      <button class="btn btn-xs" @click="incQty(p.id, p.stock)">+</button>
                    </div>
                    <button class="btn btn-primary btn-xs shrink-0" :disabled="!p.stock" @click="addToCart(p)">
                      เพิ่ม
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div v-else-if="!loading" class="text-center py-10 opacity-70">ไม่พบสินค้าในหมวดเบเกอรี่</div>
            <div v-if="loading" class="text-center py-10"><span class="loading loading-spinner loading-lg"></span></div>

            <!-- แบ่งหน้า -->
            <div class="flex items-center justify-between mt-4" v-if="total > 0">
              <div class="text-sm opacity-70">
                แสดง {{ startIdx+1 }}–{{ Math.min(total, endIdx) }} จาก {{ total }} รายการ
              </div>
              <div class="join">
                <button class="btn btn-sm join-item" @click="goto(1)" :disabled="page===1">«</button>
                <button class="btn btn-sm join-item" @click="goto(page-1)" :disabled="page===1">‹</button>
                <button v-for="n in pageButtons" :key="n"
                        class="btn btn-sm join-item" :class="{'btn-primary': n===page}"
                        @click="goto(n)">{{ n }}</button>
                <button class="btn btn-sm join-item" @click="goto(page+1)" :disabled="page===totalPages">›</button>
                <button class="btn btn-sm join-item" @click="goto(totalPages)" :disabled="page===totalPages">»</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ตะกร้า -->
    <dialog ref="cartDialog" class="modal">
      <div class="modal-box max-w-2xl">
        <h3 class="font-bold text-lg">ตะกร้าสินค้า ({{ cartCount }} รายการ)</h3>
        <div class="divider my-2"></div>

        <div v-if="cart.length===0" class="opacity-70">ยังไม่มีสินค้าในตะกร้า</div>
        <div v-else class="flex flex-col gap-2 max-h-[50vh] overflow-auto pr-1">
          <div v-for="(c, idx) in cart" :key="c.id" class="flex items-center justify-between gap-2 bg-base-200 rounded-xl p-2">
            <div class="flex items-center gap-2">
              <img :src="thumb(c)" class="w-12 h-12 object-cover rounded">
              <div>
                <div class="font-semibold leading-tight">{{ c.name }}</div>
                <div class="text-xs opacity-70">{{ money(c.price) }}</div>
              </div>
            </div>
            <div class="flex items-center gap-1">
              <button class="btn btn-xs" @click="decCart(idx)">-</button>
              <input type="number" class="input input-bordered input-xs w-14 text-center" v-model.number="c.qty" min="1" @change="saveCart()">
              <button class="btn btn-xs" @click="incCart(idx)">+</button>
            </div>
            <div class="text-right w-24 font-medium">{{ money(c.price * c.qty) }}</div>
            <button class="btn btn-error btn-xs" @click="remove(idx)">ลบ</button>
          </div>
        </div>

        <div class="divider my-2"></div>
        <div class="space-y-1">
          <div class="flex justify-between"><span>รวม</span><span class="font-semibold">{{ money(subTotal) }}</span></div>
          <div class="flex justify-between text-lg"><span>สุทธิ</span><span class="font-bold">{{ money(subTotal) }}</span></div>
        </div>

        <div class="modal-action">
          <button class="btn btn-ghost" @click="clearCart" :disabled="cart.length===0">ล้างตะกร้า</button>
          <button class="btn btn-primary" :disabled="cart.length===0" @click="checkout">
            ไปชำระเงิน
          </button>
          <form method="dialog"><button class="btn">ปิด</button></form>
        </div>
      </div>
    </dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// 🔒 ล็อกหมวดเป็น Bakery (ID = 5)
const fixedCategoryId = 5

// API base (ทำงานได้ทั้งกรณีมี /dkpos/public/)
const api = axios.create({
  baseURL: new URL('./shop/api/', window.location.href).toString(),
  withCredentials: false,
})

const products = ref([])
const total = ref(0)
const page = ref(1)
const per = ref(12)
const search = ref('')
const loading = ref(false)
const qty = ref({})

const startIdx = computed(()=> (page.value - 1) * per.value)
const endIdx   = computed(()=> startIdx.value + products.value.length)
const totalPages = computed(()=> Math.max(1, Math.ceil(total.value / per.value)))

const pageButtons = computed(()=>{
  const cur = page.value, tot = totalPages.value
  let s = Math.max(1, cur-2), e = Math.min(tot, s+4); s = Math.max(1, e-4)
  return Array.from({length: e-s+1}, (_,i)=> s+i)
})

const cartDialog = ref(null)
const cart = ref(loadCart())
const cartCount = computed(()=> cart.value.reduce((s,i)=>s+i.qty,0))
const subTotal = computed(()=> cart.value.reduce((s,i)=> s + i.price*i.qty, 0))

function thumb(p){ return p.image_path || 'https://placehold.co/600x600?text=Bakery' }
function money(n){ return new Intl.NumberFormat('th-TH',{style:'currency',currency:'THB'}).format(n ?? 0) }

async function reload(){
  loading.value = true
  try{
    const { data } = await api.get('products', {
      params: { category_id: fixedCategoryId, search: search.value, page: page.value, per_page: per.value }
    })
    total.value = data.total || 0
    products.value = data.products || []
    for(const p of products.value){ qty.value[p.id] ??= 1 }
  } finally {
    loading.value = false
  }
}

function goto(n){ if(n<1 || n>totalPages.value) return; page.value = n; reload() }

function incQty(pid, stock){ qty.value[pid] = Math.min((qty.value[pid]||1)+1, stock||9999) }
function decQty(pid){ qty.value[pid] = Math.max(1, (qty.value[pid]||1)-1) }

function addToCart(p){
  const n = Math.max(1, Math.min(qty.value[p.id] || 1, p.stock || 0))
  if (!n) return
  const i = cart.value.findIndex(x=> x.id === p.id)
  if (i>=0) cart.value[i].qty += n
  else cart.value.push({ id: p.id, name: p.name, price: p.price, image_path: p.image_path, qty: n })
  saveCart()
}

function openCart(){ cartDialog.value?.showModal() }
function clearCart(){ cart.value = []; saveCart() }
function remove(i){ cart.value.splice(i,1); saveCart() }
function incCart(i){ cart.value[i].qty++; saveCart() }
function decCart(i){ cart.value[i].qty = Math.max(1, cart.value[i].qty-1); saveCart() }

function saveCart(){ localStorage.setItem('shop_cart', JSON.stringify(cart.value)) }
function loadCart(){ try{ return JSON.parse(localStorage.getItem('shop_cart')||'[]') }catch{ return [] } }

function checkout(){
  window.location.href = new URL('./checkout', window.location.href).toString()
}

onMounted(reload)
</script>
