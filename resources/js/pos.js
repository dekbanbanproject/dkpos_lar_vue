import './bootstrap';
import { createApp } from 'vue';
import PosPage from './components/PosPage.vue';

import axios from 'axios'
axios.defaults.baseURL = import.meta.env.VITE_API_BASE || window.location.origin
axios.defaults.withCredentials = true



createApp(PosPage).mount('#app');