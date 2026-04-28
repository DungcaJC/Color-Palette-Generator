<template>
  <!--Login.vue-->
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-8">
    <div class="bg-white rounded-3xl shadow-md p-8 w-full max-w-md flex flex-col gap-5">

      <div>
        <h1 class="text-2xl font-semibold text-gray-800">Welcome back</h1>
        <p class="text-sm text-gray-400 mt-1">Login to access your saved palettes.</p>
      </div>

      <input v-model="email" type="email" placeholder="Email"
        class="Login-input w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-400" />

      <input v-model="password" type="password" placeholder="Password"
        class="Login-input w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-400"
        @keyup.enter="submit" />

      <p v-if="error && !banInfo" class="text-xs text-red-500">{{ error }}</p>

      <button @click="submit" :disabled="loading"
        class="w-full py-4 rounded-full text-white font-bold text-sm tracking-widest disabled:opacity-40 transition hover:opacity-90"
        style="background: linear-gradient(to right, #4f46e5, #f97316)">
        {{ loading ? 'LOGGING IN...' : 'LOGIN' }}
      </button>

      <p class="text-xs text-center text-gray-400">
        Don't have an account?
        <span @click="$emit('navigate', 'Signup')" class="text-indigo-500 cursor-pointer hover:underline">Sign up</span>
      </p>
    </div>
  </div>

  <!-- Ban Modal -->
  <div v-if="banInfo" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center px-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6 flex flex-col gap-4 animate-fade-in-up">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-2xl shrink-0">🚫</div>
        <div>
          <h2 class="text-lg font-bold text-gray-800 dark:text-white">Account Banned</h2>
          <p class="text-xs text-gray-400">You cannot access your account at this time.</p>
        </div>
      </div>

      <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <span class="text-xs text-red-500 font-medium uppercase tracking-widest">Ban Duration</span>
          <span class="text-sm font-bold text-red-600">{{ banInfo.ban_duration }}</span>
        </div>
        <div v-if="banInfo.ban_expires_at" class="flex items-center justify-between">
          <span class="text-xs text-red-500 font-medium uppercase tracking-widest">Expires</span>
          <span class="text-xs text-red-600">{{ formatBanDate(banInfo.ban_expires_at) }}</span>
        </div>
        <div v-else class="flex items-center justify-between">
          <span class="text-xs text-red-500 font-medium uppercase tracking-widest">Duration</span>
          <span class="text-xs text-red-600 font-semibold">Permanent</span>
        </div>
      </div>

      <div v-if="banInfo.ban_reason" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
        <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Admin Note</p>
        <p class="text-sm text-gray-600 dark:text-gray-300 italic">"{{ banInfo.ban_reason }}"</p>
      </div>

      <p class="text-xs text-gray-400 text-center">
        If you believe this is a mistake, you may appeal after your ban expires.
      </p>

      <button @click="banInfo = null" class="w-full py-2.5 rounded-xl bg-gray-800 text-white text-sm font-medium hover:bg-gray-700 transition">
        Close
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuth } from '../composables/useAuth'

const emit = defineEmits(['navigate', 'loggedIn'])
const { login } = useAuth()

const email    = ref('')
const password = ref('')
const loading  = ref(false)
const error    = ref('')
const banInfo  = ref(null)

async function submit() {
  if (!email.value || !password.value) { error.value = 'Please fill in all fields.'; return }
  loading.value = true
  error.value   = ''
  banInfo.value = null
  try {
    await login(email.value, password.value)
    emit('loggedIn')
  } catch (e) {
    const data = e?.response?.data
    if (data?.is_banned) {
      banInfo.value = data
    } else {
      error.value = data?.message || 'Login failed.'
    }
  } finally {
    loading.value = false
  }
}

function formatBanDate(iso) {
  if (!iso) return 'N/A'
  return new Date(iso).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>

<style>
.Login-input {
  transition: border-color 0.2s;
  caret-color: black;
  cursor: text;
  color: black;
}
</style>