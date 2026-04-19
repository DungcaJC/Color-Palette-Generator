<template>
    <!-- UserSettings.vue -->

  <div class="min-h-screen bg-gray-50">

    <!-- Dark Header Banner -->
    <div class="bg-[#0d1117] pt-10 pb-20 px-8 text-center">
      <div class="max-w-3xl mx-auto">
        <h1 class="text-white text-2xl font-semibold">Settings</h1>
        <p class="text-gray-400 text-sm mt-1">Customize your experience</p>
      </div>
    </div>

    <!-- Cards floating over header -->
    <div class="max-w-3xl mx-auto px-8 -mt-12 pb-16 flex flex-col gap-4">

      <!-- Preferences Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <p class="text-sm font-semibold text-gray-700">Preferences</p>
        </div>

        <!-- Dark Mode Toggle -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50">
          <div>
            <p class="text-sm text-gray-700 font-medium">Dark Mode</p>
            <p class="text-xs text-gray-400 mt-0.5">Switch between light and dark theme</p>
          </div>
          <button
            @click="toggleDarkMode"
            class="relative w-11 h-6 rounded-full transition-colors duration-300 focus:outline-none"
            :class="darkMode ? 'bg-indigo-600' : 'bg-gray-200'"
          >
            <span
              class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-300"
              :class="darkMode ? 'translate-x-5' : 'translate-x-0'"
            ></span>
          </button>
        </div>

        <!-- Notification Toggle -->
        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="text-sm text-gray-700 font-medium">Save Notifications</p>
            <p class="text-xs text-gray-400 mt-0.5">Show a notification when a palette is saved</p>
          </div>
          <button
            @click="notificationsEnabled = !notificationsEnabled; savePrefs()"
            class="relative w-11 h-6 rounded-full transition-colors duration-300 focus:outline-none"
            :class="notificationsEnabled ? 'bg-indigo-600' : 'bg-gray-200'"
          >
            <span
              class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-300"
              :class="notificationsEnabled ? 'translate-x-5' : 'translate-x-0'"
            ></span>
          </button>
        </div>
      </div>

      <!-- Data Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <p class="text-sm font-semibold text-gray-700">Your Data</p>
        </div>

        <!-- Export All -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50">
          <div>
            <p class="text-sm text-gray-700 font-medium">Export All Palettes</p>
            <p class="text-xs text-gray-400 mt-0.5">Download all your saved palettes as a JSON file</p>
          </div>
          <button
            @click="exportAll"
            class="text-sm text-indigo-600 hover:text-indigo-800 border border-indigo-200 hover:border-indigo-400 px-4 py-1.5 rounded-full transition font-medium"
          >
            Export
          </button>
        </div>

        <!-- Clear All Palettes -->
        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="text-sm text-gray-700 font-medium">Clear All Palettes</p>
            <p class="text-xs text-gray-400 mt-0.5">Permanently delete all your saved palettes</p>
          </div>
          <button
            @click="confirmClear = true"
            class="text-sm text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-4 py-1.5 rounded-full transition font-medium"
          >
            Clear
          </button>
        </div>
      </div>

      <!-- Danger Zone Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-red-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-red-50">
          <p class="text-sm font-semibold text-red-500">Danger Zone</p>
        </div>
        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="text-sm text-gray-700 font-medium">Delete Account</p>
            <p class="text-xs text-gray-400 mt-0.5">Permanently delete your account and all data. This cannot be undone.</p>
          </div>
          <button
            @click="confirmDelete = true"
            class="text-sm text-red-500 hover:text-white hover:bg-red-500 border border-red-300 hover:border-red-500 px-4 py-1.5 rounded-full transition font-medium"
          >
            Delete
          </button>
        </div>
      </div>

      <p v-if="successMsg" class="text-xs text-green-600 text-center">{{ successMsg }}</p>
    </div>

    <!-- Confirm Clear Modal -->
    <div v-if="confirmClear" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center px-4">
      <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm">
        <h2 class="text-base font-semibold text-gray-800 mb-2">Clear all palettes?</h2>
        <p class="text-sm text-gray-400 mb-6">This will permanently delete all your saved palettes. This cannot be undone.</p>
        <div class="flex gap-3">
          <button
            @click="confirmClear = false"
            class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:border-gray-400 transition"
          >
            Cancel
          </button>
          <button
            @click="clearAllPalettes"
            class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition"
          >
            Yes, clear all
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Account Modal -->
    <div v-if="confirmDelete" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center px-4">
      <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm">
        <h2 class="text-base font-semibold text-gray-800 mb-2">Delete your account?</h2>
        <p class="text-sm text-gray-400 mb-4">This is permanent and cannot be undone. All your palettes and data will be lost.</p>
        <div class="mb-4">
          <label class="text-xs text-gray-400 mb-1 block">Type <span class="font-mono font-bold text-gray-600">DELETE</span> to confirm</label>
          <input
            v-model="deleteConfirmText"
            type="text"
            placeholder="DELETE"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-red-400 transition"
          />
        </div>
        <div class="flex gap-3">
          <button
            @click="confirmDelete = false; deleteConfirmText = ''"
            class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:border-gray-400 transition"
          >
            Cancel
          </button>
          <button
            @click="deleteAccount"
            :disabled="deleteConfirmText !== 'DELETE'"
            class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 disabled:opacity-40 transition"
          >
            Delete Account
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuth } from '../composables/useAuth'
import { usePaletteStore } from '../composables/usePaletteStore'

const emit = defineEmits(['logout'])

const { logout } = useAuth()
const { getAll } = usePaletteStore()

const PREFS_KEY = 'user_preferences'
const GUEST_KEY = 'guest_saved_palettes'

const darkMode = ref(false)
const notificationsEnabled = ref(true)
const confirmClear = ref(false)
const confirmDelete = ref(false)
const deleteConfirmText = ref('')
const successMsg = ref('')

onMounted(() => {
  const prefs = JSON.parse(localStorage.getItem(PREFS_KEY) || '{}')
  darkMode.value = prefs.darkMode ?? false
  notificationsEnabled.value = prefs.notificationsEnabled ?? true
  applyDarkMode()
})

function savePrefs() {
  localStorage.setItem(PREFS_KEY, JSON.stringify({
    darkMode: darkMode.value,
    notificationsEnabled: notificationsEnabled.value,
  }))
}

function toggleDarkMode() {
  darkMode.value = !darkMode.value
  savePrefs()
  applyDarkMode()
}

function applyDarkMode() {
  document.documentElement.classList.toggle('dark', darkMode.value)
}

async function exportAll() {
  const palettes = await getAll()
  const json = JSON.stringify(palettes, null, 2)
  const blob = new Blob([json], { type: 'application/json' })
  const a = document.createElement('a')
  a.href = URL.createObjectURL(blob)
  a.download = 'my_palettes.json'
  a.click()
  successMsg.value = '✓ Palettes exported!'
  setTimeout(() => successMsg.value = '', 3000)
}

async function clearAllPalettes() {
  confirmClear.value = false
  try {
    await axios.delete('/api/palettes/all')
  } catch {}
  localStorage.removeItem(GUEST_KEY)
  localStorage.removeItem('saved_palettes')
  successMsg.value = '✓ All palettes cleared.'
  setTimeout(() => successMsg.value = '', 3000)
}

async function deleteAccount() {
  if (deleteConfirmText.value !== 'DELETE') return
  try {
    await axios.delete('/api/user')
    await logout()
    emit('logout')
  } catch {
    confirmDelete.value = false
    deleteConfirmText.value = ''
  }
}
</script>