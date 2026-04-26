<template>
  <!--Signup.vue-->
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-8">
    <div class="bg-white rounded-3xl shadow-md p-8 w-full max-w-md flex flex-col gap-5">

      <div>
        <h1 class="text-2xl font-semibold text-gray-800">Create account</h1>
        <p class="text-sm text-gray-400 mt-1">Sign up to save your color palettes.</p>
      </div>

      <input
        v-model="name"
        type="text"
        placeholder="Name"
        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-400"
      />

      <input
        v-model="email"
        type="email"
        placeholder="Email"
        class="Signup-input w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-400"
      />

      <input
        v-model="password"
        type="password"
        placeholder="Password"
        class="Signup-input w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-400"
      />

      <input
        v-model="passwordConfirmation"
        type="password"
        placeholder="Confirm password"
        class="Signup-input w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-400"
        @keyup.enter="submit"
      />

      <p v-if="error" class="text-xs text-red-500">{{ error }}</p>

      <button
        @click="submit"
        :disabled="loading"
        class="w-full py-4 rounded-full text-white font-bold text-sm tracking-widest disabled:opacity-40 transition hover:opacity-90"
        style="background: linear-gradient(to right, #4f46e5, #f97316)"
      >
        {{ loading ? 'CREATING...' : 'SIGN UP' }}
      </button>

      <p class="text-xs text-center text-gray-400">
        Already have an account?
        <span @click="$emit('navigate', 'Login')" class="text-indigo-500 cursor-pointer hover:underline">Login</span>
      </p>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuth } from '../composables/useAuth'

const emit = defineEmits(['navigate', 'loggedIn'])
const { register } = useAuth()

const name                = ref('')
const email               = ref('')
const password            = ref('')
const passwordConfirmation = ref('')
const loading             = ref(false)
const error               = ref('')

async function submit() {
  if (!name.value || !email.value || !password.value || !passwordConfirmation.value) {
    error.value = 'Please fill in all fields.'
    return
  }
  if (password.value !== passwordConfirmation.value) {
    error.value = 'Passwords do not match.'
    return
  }
  loading.value = true
  error.value = ''
  try {
    await register(name.value, email.value, password.value, passwordConfirmation.value)
    emit('loggedIn')
  } catch (e) {
    error.value = e?.response?.data?.message || 'Registration failed.'
  } finally {
    loading.value = false
  }
}
</script>

<style>
  .Signup-input {
    transition: border-color 0.2s;
    caret-color: black;
    cursor: text;
    color: black;
  }
</style>

