<template>
  <!-- AdminRoles.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <div class="bg-[#0d1117] pt-10 pb-20 px-8">
      <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-3 mb-1">
          <span class="text-2xl">⚡</span>
          <h1 class="text-white text-2xl font-semibold">Manage Roles</h1>
        </div>
        <p class="text-gray-400 text-sm ml-9">Promote or demote user roles. Super Admin access only.</p>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-8 -mt-12 pb-16 flex flex-col gap-4">

      <!-- Warning banner -->
      <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl px-5 py-4 flex items-start gap-3">
        <span class="text-lg mt-0.5">⚠️</span>
        <div>
          <p class="text-sm font-medium text-red-700 dark:text-red-400">Sensitive Action</p>
          <p class="text-xs text-red-500 dark:text-red-500 mt-0.5">Changing roles grants or removes admin access. Be careful who you promote.</p>
        </div>
      </div>

      <!-- Search -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex gap-3">
        <input
          v-model="search"
          type="text"
          placeholder="Search user by name or email..."
          class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-red-400 transition"
          @input="fetchUsers"
        />
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-24">
        <div class="w-6 h-6 border-2 border-gray-300 border-t-red-500 rounded-full animate-spin"></div>
      </div>

      <!-- User list -->
      <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-100 dark:border-gray-700">
          <p class="text-xs text-gray-400 uppercase tracking-widest">{{ users.length }} users</p>
        </div>

        <div
          v-for="u in users" :key="u.id"
          class="flex items-center justify-between px-6 py-4 border-b border-gray-50 dark:border-gray-700 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
        >
          <!-- User info -->
          <div class="flex items-center gap-3">
            <div class="relative">
              <div class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold">
                {{ u.name?.charAt(0).toUpperCase() }}
              </div>
              <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-gray-800" :class="roleColor(u.role)"></span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ u.name }}</p>
              <p class="text-xs text-gray-400">{{ u.email }}</p>
            </div>
          </div>

          <!-- Role selector -->
          <div class="flex items-center gap-3">
            <span class="text-xs text-gray-400">Current: <span class="font-medium" :class="roleLabelColor(u.role)">{{ u.role }}</span></span>

            <select
              v-if="u.role !== 'superadmin' || currentUser?.id === u.id"
              v-model="u.role"
              @change="changeRole(u)"
              :disabled="u.id === currentUser?.id"
              class="text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-1.5 focus:outline-none focus:border-red-400 transition disabled:opacity-40 cursor-pointer"
            >
              <option value="user">User</option>
              <option value="admin">Admin</option>
              <option value="superadmin">Super Admin</option>
            </select>

            <span v-if="u.id === currentUser?.id" class="text-xs text-gray-400 italic">That's you</span>
          </div>
        </div>

        <p v-if="users.length === 0" class="text-center text-gray-400 text-sm py-12">No users found</p>
      </div>

      <p v-if="msg" class="text-xs text-center" :class="msg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ msg }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuth } from '../composables/useAuth'

const { user: currentUser } = useAuth()

const users = ref([])
const loading = ref(true)
const search = ref('')
const msg = ref('')

onMounted(fetchUsers)

async function fetchUsers() {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/admin/users?search=${encodeURIComponent(search.value)}`)
    users.value = data
  } catch (e) {
    msg.value = 'Failed to load users.'
  } finally {
    loading.value = false
  }
}

async function changeRole(u) {
  try {
    await axios.patch(`/api/admin/users/${u.id}/role`, { role: u.role })
    msg.value = `✓ ${u.name}'s role updated to ${u.role}.`
    setTimeout(() => msg.value = '', 3000)
  } catch (e) {
    msg.value = e?.response?.data?.message || 'Failed to update role.'
    await fetchUsers()
  }
}

function roleColor(role) {
  if (role === 'superadmin') return 'bg-red-500'
  if (role === 'admin')      return 'bg-blue-500'
  return 'bg-green-500'
}

function roleLabelColor(role) {
  if (role === 'superadmin') return 'text-red-400'
  if (role === 'admin')      return 'text-blue-400'
  return 'text-green-400'
}
</script>