// src/composables/useAuth.js

import { ref } from 'vue'
import axios from 'axios'

const user = ref(JSON.parse(localStorage.getItem('user')) || null)
const token = ref(localStorage.getItem('token') || null)

export function useAuth() {

  async function register(name, email, password, passwordConfirmation) {
    const { data } = await axios.post('/api/register', {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    })
    setAuth(data)
  }

  async function login(email, password) {
    const { data } = await axios.post('/api/login', { email, password })
    setAuth(data)
  }

  async function logout() {
    try {
      await axios.post('/api/logout')
    } catch (e) {
      // still clear even if request fails
    } finally {
      clearAuth()
    }
  }

  function setAuth(data) {
    user.value = data.user
    token.value = data.token
    localStorage.setItem('user', JSON.stringify(data.user))
    localStorage.setItem('token', data.token)
  }

  function clearAuth() {
    user.value = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    localStorage.removeItem('guest_saved_palettes')
  }

  async function refreshUser() {
    try {
      const { data } = await axios.get('/api/me')
      user.value = data
      localStorage.setItem('user', JSON.stringify(data))
    } catch (e) {
      console.error('Failed to refresh user:', e)
    }
  }

  const isLoggedIn    = () => !!token.value
  const isAdmin       = () => ['admin', 'superadmin'].includes(user.value?.role)
  const isSuperAdmin  = () => user.value?.role === 'superadmin'

  return { user, token, isLoggedIn, isAdmin, isSuperAdmin, register, login, logout, refreshUser }

}