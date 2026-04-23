<template>
  <!-- AdminDashboard.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <!-- Header -->
    <div class="bg-[#0d1117] pt-10 pb-20 px-8">
      <div class="max-w-6xl mx-auto">
        <div class="flex items-center gap-3 mb-1">
          <span class="text-2xl">📊</span>
          <h1 class="text-white text-2xl font-semibold">Dashboard</h1>
        </div>
        <p class="text-gray-400 text-sm ml-9">Welcome back, {{ user?.name }}</p>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-8 -mt-12 pb-16 flex flex-col gap-6">

      <!-- Stat Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="stat in stats" :key="stat.label" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">{{ stat.label }}</p>
          <p class="text-3xl font-bold" :class="stat.color">
            {{ loading ? '...' : stat.value }}
          </p>
          <p class="text-xs text-gray-400 mt-1">{{ stat.sub }}</p>
        </div>
      </div>

      <!-- Two column layout -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Palettes by Source -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Palettes by Source</p>
          <div class="flex flex-col gap-3">
            <div v-for="src in sources" :key="src.label" class="flex items-center gap-3">
              <span class="text-base">{{ src.icon }}</span>
              <div class="flex-1">
                <div class="flex justify-between mb-1">
                  <span class="text-xs text-gray-500 dark:text-gray-400">{{ src.label }}</span>
                  <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ loading ? '-' : src.value }}</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                  <div
                    class="h-2 rounded-full transition-all duration-500"
                    :class="src.barColor"
                    :style="{ width: loading ? '0%' : barWidth(src.value) }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Recent Users</p>
            <button @click="$emit('navigate', 'AdminUsers')" class="text-xs text-indigo-500 hover:text-indigo-700 transition">
              View all
            </button>
          </div>
          <div class="flex flex-col gap-3">
            <div v-if="loading" class="text-xs text-gray-400 text-center py-4">Loading...</div>
            <div
              v-for="u in recentUsers" :key="u.id"
              class="flex items-center gap-3"
            >
              <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                {{ u.name?.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-700 dark:text-gray-200 truncate">{{ u.name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ u.email }}</p>
              </div>
              <div class="flex items-center gap-1.5 shrink-0">
                <span class="w-2 h-2 rounded-full" :class="roleColor(u.role)"></span>
                <span class="text-xs text-gray-400">{{ u.role }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Quick Actions</p>
        <div class="flex flex-wrap gap-3">
          <button
            @click="$emit('navigate', 'AdminUsers')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-sm font-medium hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition"
          >
            👥 Manage Users
          </button>
          <button
            @click="$emit('navigate', 'AdminPalettes')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 text-sm font-medium hover:bg-orange-100 dark:hover:bg-orange-900/50 transition"
          >
            🎨 Manage Palettes
          </button>
          <button
            v-if="isSuperAdmin()"
            @click="$emit('navigate', 'AdminRoles')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm font-medium hover:bg-red-100 dark:hover:bg-red-900/50 transition"
          >
            ⚡ Manage Roles
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

defineEmits(['navigate'])
const { user, isSuperAdmin } = useAuth()

const loading = ref(true)
const data = ref(null)
const recentUsers = ref([])

onMounted(async () => {
  try {
    const [statsRes, usersRes] = await Promise.all([
      axios.get('/api/admin/stats'),
      axios.get('/api/admin/users'),
    ])
    data.value = statsRes.data
    recentUsers.value = usersRes.data.slice(0, 5)
  } catch (e) {
    console.error('Failed to load dashboard:', e)
  } finally {
    loading.value = false
  }
})

const stats = computed(() => [
  { label: 'Total Users',    value: data.value?.total_users    ?? 0, color: 'text-indigo-500', sub: `${data.value?.new_users_this_week ?? 0} new this week` },
  { label: 'Total Palettes', value: data.value?.total_palettes ?? 0, color: 'text-orange-500', sub: 'across all users' },
  { label: 'New This Month', value: data.value?.new_users_this_month ?? 0, color: 'text-green-500', sub: 'new users' },
  { label: 'Generated',      value: (data.value?.by_source?.image ?? 0) + (data.value?.by_source?.keyword ?? 0), color: 'text-teal-500', sub: 'image + keyword' },
])

const sources = computed(() => [
  { label: 'Image',   icon: '🖼',  value: data.value?.by_source?.image   ?? 0, barColor: 'bg-orange-400' },
  { label: 'Keyword', icon: '🔍',  value: data.value?.by_source?.keyword ?? 0, barColor: 'bg-teal-400' },
  { label: 'Created', icon: '🎨',  value: data.value?.by_source?.created ?? 0, barColor: 'bg-indigo-400' },
])

const totalPalettes = computed(() => data.value?.total_palettes || 1)

function barWidth(val) {
  return Math.round((val / totalPalettes.value) * 100) + '%'
}

function roleColor(role) {
  if (role === 'superadmin') return 'bg-red-500'
  if (role === 'admin')      return 'bg-blue-500'
  return 'bg-green-500'
}
</script>