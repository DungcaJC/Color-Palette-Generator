<template>
    <!-- UserProfile.vue -->

  <div class="min-h-screen bg-gray-50">

    <!-- Dark Header Banner -->
    <div class="bg-[#0d1117] pt-10 pb-20 px-8 text-center">
      <div class="max-w-3xl mx-auto">
        <h1 class="text-white text-2xl font-semibold">Your Profile</h1>
        <p class="text-gray-400 text-sm mt-1">Manage your personal information</p>
      </div>
    </div>

    <!-- Card floating over header -->
    <div class="max-w-3xl mx-auto px-8 -mt-12 pb-16">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Avatar Section -->
        <div class="flex items-center gap-6 px-8 py-6 border-b border-gray-100">
          <!-- Avatar -->
          <div class="relative group cursor-pointer" @click="triggerAvatarUpload">
            <div
              class="w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl font-bold select-none overflow-hidden"
              :style="avatarUrl ? '' : 'background: #4f46e5'"
            >
              <img v-if="avatarUrl" :src="avatarUrl" class="w-full h-full object-cover" />
              <span v-else>{{ userInitial }}</span>
            </div>
            <!-- Overlay on hover -->
            <div class="absolute inset-0 rounded-full bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
              </svg>
            </div>
            <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="onAvatarChange" />
          </div>

          <div>
            <p class="text-base font-semibold text-gray-800">{{ user?.name || 'User' }}</p>
            <p class="text-sm text-gray-400">{{ user?.email || '' }}</p>
            <button
              @click="triggerAvatarUpload"
              class="text-xs text-indigo-500 hover:text-indigo-700 mt-1 transition"
            >
              Change photo
            </button>
          </div>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-3 divide-x divide-gray-100 border-b border-gray-100">
          <div class="px-6 py-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ stats.total }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Total Palettes</p>
          </div>
          <div class="px-6 py-4 text-center">
            <p class="text-2xl font-bold text-indigo-500">{{ stats.created }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Created</p>
          </div>
          <div class="px-6 py-4 text-center">
            <p class="text-2xl font-bold text-orange-400">{{ stats.image + stats.keyword }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Generated</p>
          </div>
        </div>

        <!-- Form Fields -->
        <div class="px-8 py-6 flex flex-col gap-5">

          <!-- Display Name -->
          <div>
            <label class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-1.5 block">Display Name</label>
            <div class="flex gap-3">
              <input
                v-model="newName"
                type="text"
                :placeholder="user?.name || 'Your name'"
                class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition"
              />
              <button
                @click="saveName"
                :disabled="!newName.trim() || newName === user?.name"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium disabled:opacity-40 hover:bg-indigo-500 transition"
              >
                Save
              </button>
            </div>
            <p v-if="nameMsg" class="text-xs text-green-500 mt-1.5">{{ nameMsg }}</p>
          </div>

          <!-- Email (read-only) -->
          <div>
            <label class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-1.5 block">Email</label>
            <input
              :value="user?.email"
              type="email"
              disabled
              class="w-full border border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed"
            />
          </div>

          <!-- Member Since -->
          <div>
            <label class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-1.5 block">Member Since</label>
            <input
              :value="memberSince"
              disabled
              class="w-full border border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed"
            />
          </div>

        </div>

        <!-- Change Password -->
        <div class="px-8 py-6 border-t border-gray-100 flex flex-col gap-4">
          <p class="text-sm font-semibold text-gray-700">Change Password</p>

          <div>
            <label class="text-xs text-gray-400 mb-1 block">Current Password</label>
            <input
              v-model="currentPassword"
              type="password"
              placeholder="••••••••"
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition"
            />
          </div>
          <div>
            <label class="text-xs text-gray-400 mb-1 block">New Password</label>
            <input
              v-model="newPassword"
              type="password"
              placeholder="••••••••"
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition"
            />
          </div>
          <div>
            <label class="text-xs text-gray-400 mb-1 block">Confirm New Password</label>
            <input
              v-model="confirmPassword"
              type="password"
              placeholder="••••••••"
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition"
            />
          </div>

          <p v-if="passwordError" class="text-xs text-red-500">{{ passwordError }}</p>
          <p v-if="passwordMsg" class="text-xs text-green-500">{{ passwordMsg }}</p>

          <button
            @click="changePassword"
            :disabled="!currentPassword || !newPassword || !confirmPassword"
            class="w-full py-3 rounded-xl bg-gray-800 text-white text-sm font-medium disabled:opacity-40 hover:bg-gray-700 transition"
          >
            Update Password
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useAuth } from '../composables/useAuth'
import { usePaletteStore } from '../composables/usePaletteStore'

const { user } = useAuth()
const { getAll } = usePaletteStore()

const avatarInput = ref(null)
const avatarUrl = ref(user.value?.avatar || null)
const newName = ref(user.value?.name || '')
const nameMsg = ref('')

const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const passwordError = ref('')
const passwordMsg = ref('')

const stats = ref({ total: 0, created: 0, image: 0, keyword: 0 })

const userInitial = computed(() => user.value?.name?.charAt(0).toUpperCase() || '?')

const memberSince = computed(() => {
  if (!user.value?.created_at) return 'N/A'
  return new Date(user.value.created_at).toLocaleDateString('en-US', {
    month: 'long', day: 'numeric', year: 'numeric'
  })
})

onMounted(async () => {
  const palettes = await getAll()
  stats.value = {
    total:   palettes.length,
    created: palettes.filter(p => p.source === 'created').length,
    image:   palettes.filter(p => p.source === 'image').length,
    keyword: palettes.filter(p => p.source === 'keyword').length,
  }
})

function triggerAvatarUpload() {
  avatarInput.value.click()
}

function onAvatarChange(e) {
  const file = e.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = ev => {
    avatarUrl.value = ev.target.result
    // TODO: upload to server via axios.post('/api/user/avatar', formData)
  }
  reader.readAsDataURL(file)
}

async function saveName() {
  if (!newName.value.trim()) return
  try {
    await axios.put('/api/user/name', { name: newName.value.trim() })
    user.value.name = newName.value.trim()
    nameMsg.value = '✓ Name updated!'
    setTimeout(() => nameMsg.value = '', 3000)
  } catch {
    nameMsg.value = 'Failed to update name.'
  }
}

async function changePassword() {
  passwordError.value = ''
  passwordMsg.value = ''
  if (newPassword.value !== confirmPassword.value) {
    passwordError.value = 'New passwords do not match.'
    return
  }
  if (newPassword.value.length < 8) {
    passwordError.value = 'Password must be at least 8 characters.'
    return
  }
  try {
    await axios.put('/api/user/password', {
      current_password: currentPassword.value,
      new_password: newPassword.value,
      new_password_confirmation: confirmPassword.value,
    })
    passwordMsg.value = '✓ Password updated successfully!'
    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
    setTimeout(() => passwordMsg.value = '', 3000)
  } catch (e) {
    passwordError.value = e.response?.data?.message || 'Incorrect current password.'
  }
}
</script>