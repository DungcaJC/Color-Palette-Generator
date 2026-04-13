import { createApp } from 'vue'
import App from './App.vue'
import axios from 'axios'
import './style.css'

axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'
console.log(import.meta.env.VITE_API_BASE_URL)

createApp(App).mount('#app')