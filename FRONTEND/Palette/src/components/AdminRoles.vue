<template>
  <!-- AdminRoles.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <div class="bg-[#0d1117] pt-8 md:pt-10 pb-16 md:pb-20 px-4 md:px-8">
      <div class="max-w-4xl mx-auto">
        <!-- Quick Actions Dropdown (Mobile & Desktop) -->
        <div class="mb-4 relative" ref="quickActionsRef">
          <button
            @click="quickActionsOpen = !quickActionsOpen"
            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/10 text-white text-sm font-medium border border-white/20 hover:bg-white/20 transition"
          >
            <span>⚡ Quick Actions</span>
            <svg class="w-4 h-4 transition-transform" :class="quickActionsOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          
          <div
            v-if="quickActionsOpen"
            class="absolute top-full mt-2 w-48 bg-gray-800 border border-white/20 rounded-lg shadow-xl overflow-hidden z-50"
          >
            <button
              @click="navigateAdmin('AdminUsers'); quickActionsOpen = false"
              class="w-full text-left px-4 py-2.5 text-white text-sm hover:bg-white/10 transition flex items-center gap-2 border-b border-white/10"
            >
              <span>👥</span> Manage Users
            </button>
            <button
              @click="navigateAdmin('AdminPalettes'); quickActionsOpen = false"
              class="w-full text-left px-4 py-2.5 text-white text-sm hover:bg-white/10 transition flex items-center gap-2 border-b border-white/10"
            >
              <span>🎨</span> Manage Palettes
            </button>
            <button
              @click="navigateAdmin('AdminReports'); quickActionsOpen = false"
              class="w-full text-left px-4 py-2.5 text-white text-sm hover:bg-white/10 transition flex items-center gap-2 border-b border-white/10"
            >
              <span>🚨</span> Reports
            </button>
            <button
              @click="navigateAdmin('AdminAppeals'); quickActionsOpen = false"
              class="w-full text-left px-4 py-2.5 text-white text-sm hover:bg-white/10 transition flex items-center gap-2 border-b border-white/10"
            >
              <span>📤</span> Appeals
            </button>
            <button
              @click="navigateAdmin('AdminRoles'); quickActionsOpen = false"
              class="w-full text-left px-4 py-2.5 text-white text-sm hover:bg-white/10 transition flex items-center gap-2"
            >
              <span>⚡</span> Manage Roles
            </button>
          </div>
        </div>

        <div class="flex items-center gap-3 mb-1"><span class="text-2xl">⚡</span><h1 class="text-white text-2xl font-semibold">Manage Roles</h1></div>
        <p class="text-gray-400 text-sm ml-9">Promote or demote user roles. Super Admin access only.</p>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-8 -mt-12 pb-16 flex flex-col gap-4">

      <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl px-5 py-4 flex items-start gap-3">
        <span class="text-lg mt-0.5">⚠️</span>
        <div>
          <p class="text-sm font-medium text-red-700 dark:text-red-400">Sensitive Action</p>
          <p class="text-xs text-red-500 mt-0.5">Changing roles grants or removes admin access. Users will be notified.</p>
        </div>
      </div>

      <!-- Search + Category filter -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex gap-3 flex-wrap">
        <input v-model="search" type="text" placeholder="Search user by name or email..."
          class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-red-400 transition min-w-48"
          @input="fetchUsers" />
        <div class="flex gap-2">
          <button v-for="cat in roleCategories" :key="cat.value" @click="roleFilter = cat.value; fetchUsers()"
            class="px-3 py-2 rounded-xl border text-xs font-medium transition"
            :class="roleFilter === cat.value ? 'border-red-400 text-red-500 bg-red-50 dark:bg-red-900/30' : 'bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:border-gray-400'"
          >{{ cat.label }}</button>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center py-24">
        <div class="w-6 h-6 border-2 border-gray-300 border-t-red-500 rounded-full animate-spin"></div>
      </div>

      <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-100 dark:border-gray-700">
          <p class="text-xs text-gray-400 uppercase tracking-widest">{{ filteredUsers.length }} users</p>
        </div>

        <div v-for="u in filteredUsers" :key="u.id"
          class="flex items-center justify-between px-6 py-4 border-b border-gray-50 dark:border-gray-700 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">

          <!-- Clickable user info -->
          <div class="flex items-center gap-3 cursor-pointer" @click="selectedUserId = u.id">
            <div class="relative">
              <div class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold overflow-hidden">
                <img v-if="u.avatar" :src="`http://localhost:8000/storage/${u.avatar}`" class="w-full h-full object-cover" />
                <span v-else>{{ u.name?.charAt(0).toUpperCase() }}</span>
              </div>
              <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-gray-800" :class="roleColor(u.role)"></span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-indigo-500 transition">{{ u.name }}</p>
              <p class="text-xs text-gray-400">{{ u.email }}</p>
            </div>
          </div>

          <!-- Role selector -->
          <div class="flex items-center gap-3">
            <span class="text-xs text-gray-400">Current: <span class="font-medium" :class="roleLabelColor(u.role)">{{ u.role }}</span></span>
            <select v-if="u.role !== 'superadmin' || currentUser?.id === u.id" v-model="u.role" @change="changeRole(u)"
              :disabled="u.id === currentUser?.id"
              class="text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-1.5 focus:outline-none focus:border-red-400 transition disabled:opacity-40 cursor-pointer">
              <option value="user">User</option>
              <option value="admin">Admin</option>
              <option value="superadmin">Super Admin</option>
            </select>
            <span v-if="u.id === currentUser?.id" class="text-xs text-gray-400 italic">That's you</span>
          </div>
        </div>

        <p v-if="filteredUsers.length === 0" class="text-center text-gray-400 text-sm py-12">No users found</p>
      </div>

      <p v-if="msg" class="text-xs text-center" :class="msg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ msg }}</p>
    </div>

    <!-- ─── User Profile Modal (shared component) ─── -->
    <UserProfileModal
      v-if="selectedUserId"
      :userId="selectedUserId"
      @close="selectedUserId = null"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useAuth } from '../composables/useAuth'
import UserProfileModal from './UserProfileModal.vue'

const emit = defineEmits(['navigate'])
const { user: currentUser } = useAuth()

const users = ref([])
const loading = ref(true)
const search = ref('')
const roleFilter = ref('all')
const msg = ref('')
const selectedUserId = ref(null)
const quickActionsOpen = ref(false)
const quickActionsRef = ref(null)

function navigateAdmin(component) {
  emit('navigate', component)
}

const roleCategories = [
  { value: 'all',        label: 'All' },
  { value: 'user',       label: '🟢 User' },
  { value: 'admin',      label: '🔵 Admin' },
  { value: 'superadmin', label: '🔴 Super Admin' },
]

const filteredUsers = computed(() => {
  if (roleFilter.value === 'all') return users.value
  return users.value.filter(u => u.role === roleFilter.value)
})

onMounted(fetchUsers)

async function fetchUsers() {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/admin/users?search=${encodeURIComponent(search.value)}`)
    users.value = data
  } catch (e) { msg.value = 'Failed to load users.' }
  finally { loading.value = false }
}

async function changeRole(u) {
  try {
    await axios.patch(`/api/admin/users/${u.id}/role`, { role: u.role })
    msg.value = `✓ ${u.name}'s role updated to ${u.role}. User notified.`
    setTimeout(() => msg.value = '', 4000)
  } catch (e) {
    msg.value = e?.response?.data?.message || 'Failed.'
    await fetchUsers()
  }
}

function roleColor(role) {
  if (role === 'superadmin') return 'bg-red-500'
  if (role === 'admin') return 'bg-blue-500'
  return 'bg-green-500'
}

function roleLabelColor(role) {
  if (role === 'superadmin') return 'text-red-400'
  if (role === 'admin') return 'text-blue-400'
  return 'text-green-400'
}
</script>