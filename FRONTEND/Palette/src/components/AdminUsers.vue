<template>
  <!-- AdminUsers.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <div class="bg-[#0d1117] pt-8 md:pt-10 pb-16 md:pb-20 px-4 md:px-8">
      <div class="max-w-6xl mx-auto">
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

        <div class="flex items-center gap-3 mb-1"><span class="text-2xl">👥</span><h1 class="text-white text-2xl font-semibold">Manage Users</h1></div>
        <p class="text-gray-400 text-sm ml-9">View, ban, and manage user accounts</p>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-8 -mt-12 pb-16 flex flex-col gap-4">

      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex gap-3">
        <input v-model="search" type="text" placeholder="Search by name or email..." class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-400 transition" @input="fetchUsers" />
        <button @click="fetchUsers" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-500 transition">Search</button>
      </div>

      <div v-if="loading" class="flex justify-center py-24">
        <div class="w-6 h-6 border-2 border-gray-300 border-t-indigo-600 rounded-full animate-spin"></div>
      </div>

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
                <td class="px-6 py-4 cursor-pointer" @click="selectedUserId = u.id">
                  <div class="flex items-center gap-3">
                    <div class="relative">
                      <div class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold overflow-hidden shrink-0">
                        <img v-if="u.avatar" :src="getImageUrl(u.avatar)" class="w-full h-full object-cover" />
                        <span v-else>{{ u.name?.charAt(0).toUpperCase() }}</span>
                      </div>
                      <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-gray-800" :class="roleColor(u.role)"></span>
                    </div>
                    <div>
                      <p class="font-medium text-gray-700 dark:text-gray-200 hover:text-indigo-500 transition">{{ u.name }}</p>
                      <p class="text-xs text-gray-400">{{ u.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium" :class="roleBadge(u.role)">
                    <span class="w-1.5 h-1.5 rounded-full" :class="roleColor(u.role)"></span>{{ u.role }}
                  </span>
                </td>
                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ u.palettes_count }}</td>
                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs">{{ formatDate(u.created_at) }}</td>
                <td class="px-6 py-4">
                  <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium" :class="u.is_banned ? 'bg-red-50 dark:bg-red-900/30 text-red-500' : 'bg-green-50 dark:bg-green-900/30 text-green-500'">
                    {{ u.is_banned ? 'Banned' : 'Active' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button v-if="u.role !== 'superadmin'" @click="openBanModal(u)" class="text-xs px-3 py-1.5 rounded-full border transition" :class="u.is_banned ? 'border-green-200 text-green-500 hover:border-green-400' : 'border-amber-200 text-amber-500 hover:border-amber-400'">{{ u.is_banned ? 'Unban' : 'Ban' }}</button>
                    <button v-if="u.role !== 'superadmin'" @click="confirmDelete(u)" class="text-xs px-3 py-1.5 rounded-full border border-red-200 text-red-400 hover:border-red-400 transition">Delete</button>
                    <span v-if="u.role === 'superadmin'" class="text-xs text-gray-400 italic">Protected</span>
                  </div>
                </td>
              </tr>
              <tr v-if="users.length === 0"><td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">No users found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <p v-if="msg" class="text-xs text-center" :class="msg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ msg }}</p>
    </div>

    <!-- Ban Duration Modal -->
    <div v-if="banTarget" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center px-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 flex flex-col gap-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-800 dark:text-white">Ban {{ banTarget.name }}</h2>
          <button @click="banTarget = null" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3">
          <p class="text-xs text-amber-700 dark:text-amber-400">This will prevent the user from logging in until the ban expires.</p>
        </div>

        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Ban Duration</p>
          <div class="grid grid-cols-3 gap-2">
            <button v-for="d in banDurations" :key="d.value" @click="banForm.duration = d.value"
              class="py-2.5 rounded-xl border text-xs font-medium transition text-center"
              :class="banForm.duration === d.value ? 'bg-red-500 text-white border-red-500' : 'bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:border-red-300'"
            >{{ d.label }}</button>
          </div>
        </div>

        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Admin Note (optional)</p>
          <textarea v-model="banForm.admin_reason" placeholder="Reason for ban..." rows="2"
            class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none resize-none transition" style="white-space: pre-wrap; word-break: break-word;"></textarea>
        </div>

        <p v-if="banMsg" class="text-xs" :class="banMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ banMsg }}</p>

        <div class="flex gap-3">
          <button @click="banTarget = null" class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 transition">Cancel</button>
          <button @click="executeBan" :disabled="!banForm.duration || banning"
            class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium disabled:opacity-40 hover:bg-red-600 transition">
            {{ banning ? 'Banning...' : '🚫 Ban User' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ─── User Profile Modal (shared component) ─── -->
    <UserProfileModal
      v-if="selectedUserId"
      :userId="selectedUserId"
      @close="selectedUserId = null"
    />

    <!-- Confirm Delete Modal -->
    <div v-if="deleteTarget" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center px-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-sm">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-2">Delete user?</h2>
        <p class="text-sm text-gray-400 mb-6">Permanently delete <span class="font-medium text-gray-700 dark:text-gray-200">{{ deleteTarget.name }}</span> and all their data.</p>
        <div class="flex gap-3">
          <button @click="deleteTarget = null" class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 transition">Cancel</button>
          <button @click="deleteUser" class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition">Delete</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import UserProfileModal from './UserProfileModal.vue'

const emit = defineEmits(['navigate'])

function getImageUrl(path) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

const users = ref([])
const loading = ref(true)
const search = ref('')
const msg = ref('')
const deleteTarget = ref(null)
const banTarget  = ref(null)
const banMsg     = ref('')
const banning    = ref(false)
const banForm    = ref({ duration: '', admin_reason: '' })
const selectedUserId = ref(null)
const quickActionsOpen = ref(false)
const quickActionsRef = ref(null)

const banDurations = [
  { value: '1d', label: '1 Day'     },
  { value: '3d', label: '3 Days'    },
  { value: '1w', label: '1 Week'    },
  { value: '1m', label: '1 Month'   },
  { value: '3m', label: '3 Months'  },
  { value: '1y', label: '1 Year'    },
  { value: 'permanent', label: 'Permanent' },
]

function navigateAdmin(component) {
  emit('navigate', component)
}

onMounted(fetchUsers)

async function fetchUsers() {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/admin/users?search=${encodeURIComponent(search.value)}`)
    users.value = data
  } catch (e) { msg.value = 'Failed to load users.' }
  finally { loading.value = false }
}

function openBanModal(u) {
  // If already banned, unban immediately
  if (u.is_banned) {
    toggleUnban(u)
    return
  }
  banTarget.value = u
  banForm.value = { duration: '1d', admin_reason: '' }
  banMsg.value = ''
}

async function toggleUnban(u) {
  try {
    const { data } = await axios.patch(`/api/admin/users/${u.id}/ban`)
    u.is_banned = data.is_banned
    msg.value = `✓ ${data.message}`
    setTimeout(() => msg.value = '', 3000)
  } catch (e) { msg.value = e?.response?.data?.message || 'Failed.' }
}

async function executeBan() {
  if (!banForm.value.duration) return
  banning.value = true
  banMsg.value  = ''
  try {
    await axios.patch(`/api/admin/users/${banTarget.value.id}/ban`, {
      duration:     banForm.value.duration,
      admin_reason: banForm.value.admin_reason,
    })
    banTarget.value.is_banned = true
    msg.value = `✓ User banned.`
    banTarget.value = null
    setTimeout(() => msg.value = '', 3000)
  } catch (e) {
    banMsg.value = e?.response?.data?.message || 'Failed.'
  } finally {
    banning.value = false
  }
}

function confirmDelete(u) { deleteTarget.value = u }

async function deleteUser() {
  try {
    await axios.delete(`/api/admin/users/${deleteTarget.value.id}`)
    users.value = users.value.filter(u => u.id !== deleteTarget.value.id)
    msg.value = '✓ User deleted.'
    deleteTarget.value = null
    setTimeout(() => msg.value = '', 3000)
  } catch (e) { msg.value = e?.response?.data?.message || 'Failed.' }
}

function formatDate(iso) {
  if (!iso) return '-'
  return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function roleColor(role) {
  if (role === 'superadmin') return 'bg-red-500'
  if (role === 'admin') return 'bg-blue-500'
  return 'bg-green-500'
}

function roleBadge(role) {
  if (role === 'superadmin') return 'bg-red-50 dark:bg-red-900/30 text-red-500'
  if (role === 'admin') return 'bg-blue-50 dark:bg-blue-900/30 text-blue-500'
  return 'bg-green-50 dark:bg-green-900/30 text-green-500'
}
</script>