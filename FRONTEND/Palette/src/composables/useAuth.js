import { ref } from 'vue'
import axios from 'axios'

const user = ref(JSON.parse(localStorage.getItem('user')) || null)
const token = ref(localStorage.getItem('token') || null)

if (token.value) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
}

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
    await axios.post('/api/logout')
    clearAuth()
  }

  function setAuth(data) {
    user.value = data.user
    token.value = data.token
    localStorage.setItem('user', JSON.stringify(data.user))
    localStorage.setItem('token', data.token)
    axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`
  }

  function clearAuth() {
    user.value = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
  }

  const isLoggedIn = () => !!token.value

  return { user, token, isLoggedIn, register, login, logout }
}