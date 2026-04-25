<template>
  <!--Login.vue-->

  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-8">
    <div class="bg-white rounded-3xl shadow-md p-8 w-full max-w-md flex flex-col gap-5">

      <div>
        <h1 class="text-2xl font-semibold text-gray-800">Welcome back</h1>
        <p class="text-sm text-gray-400 mt-1">Login to access your saved palettes.</p>
      </div>

      <input
        v-model="email"
        type="email"
        placeholder="Email"
        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-400"
      />

      <input
        v-model="password"
        type="password"
        placeholder="Password"
        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-400"
        @keyup.enter="submit"
      />

      <p v-if="error" class="text-xs text-red-500">{{ error }}</p>

      <button
        @click="submit"
        :disabled="loading"
        class="w-full py-4 rounded-full text-white font-bold text-sm tracking-widest disabled:opacity-40 transition hover:opacity-90"
        style="background: linear-gradient(to right, #4f46e5, #f97316)"
      >
        {{ loading ? 'LOGGING IN...' : 'LOGIN' }}
      </button>

      <p class="text-xs text-center text-gray-400">
        Don't have an account?
        <span @click="$emit('navigate', 'Signup')" class="text-indigo-500 cursor-pointer hover:underline">Sign up</span>
      </p>

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

async function submit() {
  if (!email.value || !password.value) {
    error.value = 'Please fill in all fields.'
    return
  }
  loading.value = true
  error.value = ''
  try {
    await login(email.value, password.value)
    emit('loggedIn')
  } catch (e) {
    error.value = e?.response?.data?.message || 'Login failed.'
  } finally {
    loading.value = false
  }
}
</script>