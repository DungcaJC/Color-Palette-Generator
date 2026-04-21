<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <div class="bg-[#0d1117] pt-10 pb-20 px-8 text-center">
      <div class="max-w-3xl mx-auto">
        <h1 class="text-white text-2xl font-semibold">Settings</h1>
        <p class="text-gray-400 text-sm mt-1">Customize your experience</p>
      </div>
    </div>

    <div class="max-w-3xl mx-auto px-8 -mt-12 pb-16 flex flex-col gap-4">

      <!-- Preferences -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
          <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Preferences</p>
        </div>

        <!-- Dark Mode -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 dark:border-gray-700">
          <div>
            <p class="text-sm text-gray-700 dark:text-gray-200 font-medium">Dark Mode</p>
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

        <!-- Notifications -->
        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="text-sm text-gray-700 dark:text-gray-200 font-medium">Save Notifications</p>
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

      <!-- Data -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
          <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Your Data</p>
        </div>

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 dark:border-gray-700">
          <div>
            <p class="text-sm text-gray-700 dark:text-gray-200 font-medium">Export All Palettes</p>
            <p class="text-xs text-gray-400 mt-0.5">Download all your saved palettes as a JSON file</p>
          </div>
          <button
            @click="exportAll"
            class="text-sm text-indigo-600 hover:text-indigo-800 border border-indigo-200 hover:border-indigo-400 px-4 py-1.5 rounded-full transition font-medium"
          >
            Export
          </button>
        </div>

        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="text-sm text-gray-700 dark:text-gray-200 font-medium">Clear All Palettes</p>
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

      <!-- Danger Zone -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-red-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-red-50">
          <p class="text-sm font-semibold text-red-500">Danger Zone</p>
        </div>
        <div class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="text-sm text-gray-700 dark:text-gray-200 font-medium">Delete Account</p>
            <p class="text-xs text-gray-400 mt-0.5">Permanently delete your account and all data.</p>
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
        <p class="text-sm text-gray-400 mb-6">This will permanently delete all your saved palettes.</p>
        <div class="flex gap-3">
          <button @click="confirmClear = false" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:border-gray-400 transition">
            Cancel
          </button>
          <button @click="clearAllPalettes" class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition">
            Yes, clear all
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Account Modal -->
    <div v-if="confirmDelete" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center px-4">
      <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm">
        <h2 class="text-base font-semibold text-gray-800 mb-2">Delete your account?</h2>
        <p class="text-sm text-gray-400 mb-4">This is permanent and cannot be undone.</p>
        <div class="mb-4">
          <label class="text-xs text-gray-400 mb-1 block">Type <span class="font-mono font-bold text-gray-600">DELETE</span> to confirm</label>
          <input
            v-model="deleteConfirmText"
            type="text"
            placeholder="DELETE"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-red-400 transition"
          />
        </div>
        <p v-if="deleteError" class="text-xs text-red-500 mb-3">{{ deleteError }}</p>
        <div class="flex gap-3">
          <button @click="confirmDelete = false; deleteConfirmText = ''" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:border-gray-400 transition">
            Cancel
          </button>
          <button
            @click="deleteAccount"
            :disabled="deleteConfirmText !== 'DELETE' || deleting"
            class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 disabled:opacity-40 transition"
          >
            {{ deleting ? 'Deleting...' : 'Delete Account' }}
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
const { getAll, clearAll } = usePaletteStore()

const PREFS_KEY = 'user_preferences'

const darkMode = ref(false)
const notificationsEnabled = ref(true)
const confirmClear = ref(false)
const confirmDelete = ref(false)
const deleteConfirmText = ref('')
const deleteError = ref('')
const deleting = ref(false)
const successMsg = ref('')

onMounted(() => {
  const prefs = JSON.parse(localStorage.getItem(PREFS_KEY) || '{}')
  
  // ← read the actual current state of the document, not just prefs
  darkMode.value = document.documentElement.classList.contains('dark')
  notificationsEnabled.value = prefs.notificationsEnabled ?? true
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
  if (darkMode.value) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

async function exportAll() {
  const palettes = await getAll()
  const blob = new Blob([JSON.stringify(palettes, null, 2)], { type: 'application/json' })
  const a = document.createElement('a')
  a.href = URL.createObjectURL(blob)
  a.download = 'my_palettes.json'
  a.click()
  successMsg.value = '✓ Palettes exported!'
  setTimeout(() => successMsg.value = '', 3000)
}

async function clearAllPalettes() {
  confirmClear.value = false
  await clearAll()
  successMsg.value = '✓ All palettes cleared.'
  setTimeout(() => successMsg.value = '', 3000)
}

async function deleteAccount() {
  if (deleteConfirmText.value !== 'DELETE') return
  deleting.value = true
  deleteError.value = ''
  try {
    await axios.delete('/api/user')
    await logout()
    emit('logout')
  } catch (e) {
    deleteError.value = e?.response?.data?.message || 'Failed to delete account.'
    deleting.value = false
  }
}
</script>