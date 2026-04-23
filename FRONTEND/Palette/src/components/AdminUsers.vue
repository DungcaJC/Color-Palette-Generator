<template>
  <!-- AdminUsers.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <div class="bg-[#0d1117] pt-10 pb-20 px-8">
      <div class="max-w-6xl mx-auto">
        <div class="flex items-center gap-3 mb-1">
          <span class="text-2xl">👥</span>
          <h1 class="text-white text-2xl font-semibold">Manage Users</h1>
        </div>
        <p class="text-gray-400 text-sm ml-9">View, ban, and manage user accounts</p>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-8 -mt-12 pb-16 flex flex-col gap-4">

      <!-- Search bar -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex gap-3">
        <input
          v-model="search"
          type="text"
          placeholder="Search by name or email..."
          class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-400 transition"
          @input="fetchUsers"
        />
        <button
          @click="fetchUsers"
          class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-500 transition"
        >
          Search
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-24">
        <div class="w-6 h-6 border-2 border-gray-300 border-t-indigo-600 rounded-full animate-spin"></div>
      </div>

      <!-- User Table -->
      <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-700">
                <th class="text-left px-6 py-3 text-xs text-gray-400 uppercase tracking-widest font-medium">User</th>
                <th class="text-left px-6 py-3 text-xs text-gray-400 uppercase tracking-widest font-medium">Role</th>
                <th class="text-left px-6 py-3 text-xs text-gray-400 uppercase tracking-widest font-medium">Palettes</th>
                <th class="text-left px-6 py-3 text-xs text-gray-400 uppercase tracking-widest font-medium">Joined</th>
                <th class="text-left px-6 py-3 text-xs text-gray-400 uppercase tracking-widest font-medium">Status</th>
                <th class="text-right px-6 py-3 text-xs text-gray-400 uppercase tracking-widest font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="u in users" :key="u.id"
                class="border-b border-gray-50 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                :class="u.is_banned ? 'opacity-60' : ''"
              >
                <!-- User info -->
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="relative">
                      <div class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold shrink-0">
                        {{ u.name?.charAt(0).toUpperCase() }}
                      </div>
                      <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-gray-800" :class="roleColor(u.role)"></span>
                    </div>
                    <div>
                      <p class="font-medium text-gray-700 dark:text-gray-200">{{ u.name }}</p>
                      <p class="text-xs text-gray-400">{{ u.email }}</p>
                    </div>
                  </div>
                </td>

                <!-- Role -->
                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
                    :class="roleBadge(u.role)"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="roleColor(u.role)"></span>
                    {{ u.role }}
                  </span>
                </td>

                <!-- Palettes count -->
                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                  {{ u.palettes_count }}
                </td>

                <!-- Joined -->
                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs">
                  {{ formatDate(u.created_at) }}
                </td>

                <!-- Status -->
                <td class="px-6 py-4">
                  <span
                    class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                    :class="u.is_banned ? 'bg-red-50 dark:bg-red-900/30 text-red-500' : 'bg-green-50 dark:bg-green-900/30 text-green-500'"
                  >
                    {{ u.is_banned ? 'Banned' : 'Active' }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <!-- Ban/Unban -->
                    <button
                      v-if="u.role !== 'superadmin'"
                      @click="toggleBan(u)"
                      class="text-xs px-3 py-1.5 rounded-full border transition"
                      :class="u.is_banned
                        ? 'border-green-200 text-green-500 hover:border-green-400'
                        : 'border-amber-200 text-amber-500 hover:border-amber-400'"
                    >
                      {{ u.is_banned ? 'Unban' : 'Ban' }}
                    </button>

                    <!-- Delete -->
                    <button
                      v-if="u.role !== 'superadmin'"
                      @click="confirmDelete(u)"
                      class="text-xs px-3 py-1.5 rounded-full border border-red-200 text-red-400 hover:border-red-400 transition"
                    >
                      Delete
                    </button>

                    <span v-if="u.role === 'superadmin'" class="text-xs text-gray-400 italic">Protected</span>
                  </div>
                </td>
              </tr>

              <tr v-if="users.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">No users found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <p v-if="msg" class="text-xs text-center" :class="msg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ msg }}</p>
    </div>

    <!-- Confirm Delete Modal -->
    <div v-if="deleteTarget" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center px-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-sm">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-2">Delete user?</h2>
        <p class="text-sm text-gray-400 mb-6">
          This will permanently delete <span class="font-medium text-gray-700 dark:text-gray-200">{{ deleteTarget.name }}</span> and all their palettes.
        </p>
        <div class="flex gap-3">
          <button @click="deleteTarget = null" class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 hover:border-gray-400 transition">
            Cancel
          </button>
          <button @click="deleteUser" class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition">
            Delete
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

defineEmits(['navigate'])

const users = ref([])
const loading = ref(true)
const search = ref('')
const msg = ref('')
const deleteTarget = ref(null)

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

async function toggleBan(u) {
  try {
    const { data } = await axios.patch(`/api/admin/users/${u.id}/ban`)
    u.is_banned = data.is_banned
    msg.value = `✓ ${data.message}`
    setTimeout(() => msg.value = '', 3000)
  } catch (e) {
    msg.value = e?.response?.data?.message || 'Failed.'
  }
}

function confirmDelete(u) {
  deleteTarget.value = u
}

async function deleteUser() {
  try {
    await axios.delete(`/api/admin/users/${deleteTarget.value.id}`)
    users.value = users.value.filter(u => u.id !== deleteTarget.value.id)
    msg.value = '✓ User deleted.'
    deleteTarget.value = null
    setTimeout(() => msg.value = '', 3000)
  } catch (e) {
    msg.value = e?.response?.data?.message || 'Failed to delete.'
  }
}

function formatDate(iso) {
  if (!iso) return '-'
  return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function roleColor(role) {
  if (role === 'superadmin') return 'bg-red-500'
  if (role === 'admin')      return 'bg-blue-500'
  return 'bg-green-500'
}

function roleBadge(role) {
  if (role === 'superadmin') return 'bg-red-50 dark:bg-red-900/30 text-red-500'
  if (role === 'admin')      return 'bg-blue-50 dark:bg-blue-900/30 text-blue-500'
  return 'bg-green-50 dark:bg-green-900/30 text-green-500'
}
</script>