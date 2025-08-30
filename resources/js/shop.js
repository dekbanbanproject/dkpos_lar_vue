import { createApp } from 'vue'
import ShopHome from './components/ShopHome.vue'

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('shop')
  if (el) {
    createApp(ShopHome).mount(el)
    console.log('✅ Shop mounted')
  } else {
    console.warn('⚠️ #shop not found')
  }
})
