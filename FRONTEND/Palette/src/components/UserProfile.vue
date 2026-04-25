<template>
  <!--UserProfile.vue-->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <div class="bg-[#0d1117] pt-10 pb-20 px-8 text-center">
      <div class="max-w-3xl mx-auto">
        <h1 class="text-white text-2xl font-semibold">Your Profile</h1>
        <p class="text-gray-400 text-sm mt-1">Manage your personal information</p>
      </div>
    </div>

    <div class="max-w-3xl mx-auto px-8 -mt-12 pb-16">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">

        <!-- Avatar -->
        <div class="flex items-center gap-6 px-8 py-6 border-b border-gray-100 dark:border-gray-700">
          <div class="relative group cursor-pointer" @click="triggerAvatarUpload">
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl font-bold select-none overflow-hidden bg-indigo-600">
              <img v-if="avatarUrl" :src="avatarUrl" class="w-full h-full object-cover" />
              <span v-else>{{ userInitial }}</span>
            </div>
            <div class="absolute inset-0 rounded-full bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
              </svg>
            </div>
            <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="onAvatarChange" />
          </div>
          <div>
            <p class="text-base font-semibold text-gray-800 dark:text-white">{{ user?.name || 'User' }}</p>
            <p class="text-sm text-gray-400">{{ user?.email || '' }}</p>
            <p v-if="avatarMsg" class="text-xs mt-1" :class="avatarMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ avatarMsg }}</p>
            <button v-else @click="triggerAvatarUpload" class="text-xs text-indigo-500 hover:text-indigo-700 mt-1 transition">Change photo</button>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-700 border-b border-gray-100 dark:border-gray-700">
          <div class="px-6 py-4 text-center">
            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ stats.total }}</p>
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

        <!-- Tab bar -->
        <div class="flex border-b border-gray-100 dark:border-gray-700">
          <button @click="activeTab = 'profile'" class="flex-1 py-3 text-sm font-medium transition border-b-2" :class="activeTab === 'profile' ? 'border-indigo-500 text-indigo-500' : 'border-transparent text-gray-400 hover:text-gray-600'">
            👤 Profile
          </button>
          <button @click="activeTab = 'posts'" class="flex-1 py-3 text-sm font-medium transition border-b-2" :class="activeTab === 'posts' ? 'border-indigo-500 text-indigo-500' : 'border-transparent text-gray-400 hover:text-gray-600'">
            🖼 My Posts ({{ myPosts.length }})
          </button>
        </div>

        <!-- Posts tab -->
        <div v-if="activeTab === 'posts'" class="p-6">
          <div v-if="myPosts.length === 0" class="text-center py-8 text-gray-400 text-sm">You haven't posted anything yet.</div>
          <div class="grid grid-cols-3 gap-3">
            <div
              v-for="post in myPosts" :key="post.id"
              @click="activePost = post"
              class="aspect-square rounded-xl overflow-hidden cursor-pointer hover:opacity-80 transition bg-gray-100 dark:bg-gray-700"
            >
              <img :src="`http://localhost:8000/storage/${post.image}`" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>
        
        <!-- Bio section -->
        <div>
          <label class="text-xs font-medium text-gray-400 uppercase tracking-widest m-4 block text-center">Bio</label>
          <textarea
            v-model="bio"
            maxlength="500"
            rows="3"
            placeholder="Tell the community about yourself..."
            @input="autoResize($event)"
            class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition resize-none overflow-hidden"
            style="min-height: 80px; max-height: 200px;"
          ></textarea>
          <div class="flex items-center justify-between mt-2">
            <span class="text-xs text-gray-400 ms-3">{{ bioLength }}/500</span>
            <button
              @click="saveBio"
              class="flex items-center gap-1.5 px-4 py-2 text-white text-xs font-semibold transition hover:opacity-90 me-3 rounded-xl"
              style="background: #427cf0;"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              Save Bio
            </button>
          </div>
          <p v-if="bioMsg" class="text-xs mt-1" :class="bioMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ bioMsg }}</p>

          <!-- Form Fields -->
          <div class="px-8 py-6 flex flex-col gap-5">
            <div>
              <label class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-1.5 block">Display Name</label>
              <div class="flex gap-3">
                <input
                  v-model="newName"
                  type="text"
                  :placeholder="user?.name || 'Your name'"
                  class="flex-1 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition"
                />
                <button
                  @click="saveName"
                  :disabled="!newName.trim() || newName === user?.name"
                  class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium disabled:opacity-40 hover:bg-indigo-500 transition"
                >
                  Save
                </button>
              </div>
              <p v-if="nameMsg" class="text-xs mt-1.5" :class="nameMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ nameMsg }}</p>
            </div>

            <div>
              <label class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-1.5 block">Email</label>
              <input :value="user?.email" type="email" disabled class="w-full border border-gray-100 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed" />
            </div>

            <div>
              <label class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-1.5 block">Member Since</label>
              <input :value="memberSince" disabled class="w-full border border-gray-100 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed" />
            </div>
          </div>

          <!-- Change Password -->
          <div class="px-8 py-6 border-t border-gray-100 dark:border-gray-700 flex flex-col gap-4">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Change Password</p>
            <div>
              <label class="text-xs text-gray-400 mb-1 block">Current Password</label>
              <input v-model="currentPassword" type="password" placeholder="••••••••" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition" />
            </div>
            <div>
              <label class="text-xs text-gray-400 mb-1 block">New Password</label>
              <input v-model="newPassword" type="password" placeholder="••••••••" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition" />
            </div>
            <div>
              <label class="text-xs text-gray-400 mb-1 block">Confirm New Password</label>
              <input v-model="confirmPassword" type="password" placeholder="••••••••" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition" />
            </div>
            <p v-if="passwordError" class="text-xs text-red-500">{{ passwordError }}</p>
            <p v-if="passwordMsg" class="text-xs text-green-500">{{ passwordMsg }}</p>
            <button
              @click="changePassword"
              :disabled="!currentPassword || !newPassword || !confirmPassword"
              class="w-full py-3 rounded-xl bg-gray-800 dark:bg-gray-600 text-white text-sm font-medium disabled:opacity-40 hover:bg-gray-700 transition"
            >
              Update Password
            </button>
          </div>
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
const avatarUrl = ref(user.value?.avatar ? `http://localhost:8000/storage/${user.value.avatar}` : null)
const avatarMsg = ref('')
const newName = ref(user.value?.name || '')
const nameMsg = ref('')
const bio = ref(user.value?.bio || '')
const bioMsg = ref('')
const bioLength = computed(() => bio.value.length)
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const passwordError = ref('')
const passwordMsg = ref('')
const stats = ref({ total: 0, created: 0, image: 0, keyword: 0 })
const activeTab = ref('profile')
const myPosts = ref([])
const activePost = ref(null)

const userInitial = computed(() => user.value?.name?.charAt(0).toUpperCase() || '?')
const memberSince = computed(() => {
  if (!user.value?.created_at) return 'N/A'
  return new Date(user.value.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
})

onMounted(async () => {
  const palettes = await getAll()
  stats.value = {
    total:   palettes.length,
    created: palettes.filter(p => p.source === 'created').length,
    image:   palettes.filter(p => p.source === 'image').length,
    keyword: palettes.filter(p => p.source === 'keyword').length,
  }
  // load my posts
  try {
    const { data } = await axios.get('/api/my-posts')
    myPosts.value = data
  } catch (e) { console.error(e) }
})

function triggerAvatarUpload() { avatarInput.value.click() }

async function onAvatarChange(e) {
  const file = e.target.files[0]
  if (!file) return
  if (file.size > 2 * 1024 * 1024) { avatarMsg.value = 'Image must be under 2MB.'; return }
  const reader = new FileReader()
  reader.onload = ev => { avatarUrl.value = ev.target.result }
  reader.readAsDataURL(file)
  try {
    const formData = new FormData()
    formData.append('avatar', file)
    const { data } = await axios.post('/api/user/avatar', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    if (user.value) user.value.avatar = data.avatar
    avatarUrl.value = `http://localhost:8000/storage/${data.avatar}`
    avatarMsg.value = '✓ Photo updated!'
    setTimeout(() => avatarMsg.value = '', 3000)
  } catch (e) {
    avatarMsg.value = 'Failed to upload.'
    setTimeout(() => avatarMsg.value = '', 3000)
  }
}

async function saveName() {
  if (!newName.value.trim()) return
  try {
    await axios.put('/api/user/name', { name: newName.value.trim() })
    if (user.value) user.value.name = newName.value.trim()
    nameMsg.value = '✓ Name updated!'
    setTimeout(() => nameMsg.value = '', 3000)
  } catch { nameMsg.value = 'Failed.' }
}

async function saveBio() {
  try {
    await axios.put('/api/user/bio', { bio: bio.value })
    if (user.value) user.value.bio = bio.value
    bioMsg.value = '✓ Bio updated!'
    setTimeout(() => bioMsg.value = '', 3000)
  } catch { bioMsg.value = 'Failed.' }
}

async function changePassword() {
  passwordError.value = ''
  passwordMsg.value = ''
  if (newPassword.value !== confirmPassword.value) { passwordError.value = 'Passwords do not match.'; return }
  if (newPassword.value.length < 8) { passwordError.value = 'Must be at least 8 characters.'; return }
  try {
    await axios.put('/api/user/password', { current_password: currentPassword.value, new_password: newPassword.value })
    passwordMsg.value = '✓ Password updated!'
    currentPassword.value = ''; newPassword.value = ''; confirmPassword.value = ''
    setTimeout(() => passwordMsg.value = '', 3000)
  } catch (e) {
    passwordError.value = e?.response?.data?.message || 'Incorrect current password.'
  }
}
</script>