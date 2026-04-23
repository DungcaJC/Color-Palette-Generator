// main.js
import { createApp } from 'vue'
import App from './App.vue'
import axios from 'axios'
import './style.css'

axios.defaults.baseURL = 'http://localhost:8000'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

// ← NO withCredentials here, we use Bearer tokens not cookies

axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers['Authorization'] = `Bearer ${token}`
  }
  return config
})

createApp(App).mount('#app')