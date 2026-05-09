<template>
  <!-- AdminDashboard.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <div class="bg-[#0d1117] pt-10 pb-20 px-8">
      <div class="max-w-6xl mx-auto flex items-center justify-between">
        <div>
          <div class="flex items-center gap-3 mb-1">
            <span class="text-2xl">📊</span>
            <h1 class="text-white text-2xl font-semibold">Dashboard</h1>
          </div>
          <p class="text-gray-400 text-sm ml-9">Welcome back, {{ user?.name }}</p>
        </div>
        <div class="flex gap-2">
          <button
            v-for="tab in chartTabs" :key="tab.value"
            @click="chartMode = tab.value; loadChart()"
            class="px-3 py-1.5 rounded-lg text-xs font-medium transition"
            :class="chartMode === tab.value ? 'bg-white text-gray-900' : 'bg-white/10 text-gray-400 hover:text-white'"
          >
            {{ tab.label }}
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-8 -mt-12 pb-16 flex flex-col gap-5">

      <!-- Stat Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="stat in stats" :key="stat.label" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">{{ stat.label }}</p>
          <p class="text-3xl font-bold" :class="stat.color">{{ loading ? '...' : stat.value }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ stat.sub }}</p>
        </div>
      </div>

      <!-- Chart -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
          <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Activity — {{ chartMode === 'daily' ? 'Last 7 Days' : 'Last 12 Months' }}</p>
          <div class="flex gap-4 text-xs">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-indigo-500 inline-block"></span> Users</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-orange-400 inline-block"></span> Posts</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-teal-400 inline-block"></span> Palettes</span>
          </div>
        </div>
        <div class="overflow-x-auto -mx-2 px-2" v-if="chartData.length">
          <div class="flex items-end gap-2 h-32" style="min-width:360px">
            <div v-for="(d, i) in chartData" :key="i" class="flex-1 flex flex-col items-center gap-0.5">
              <div class="w-full flex gap-0.5 items-end h-24">
                <div class="flex-1 bg-indigo-500 rounded-t transition-all" :style="{ height: barHeight(d.users) + '%' }" :title="`${d.users} users`"></div>
                <div class="flex-1 bg-orange-400 rounded-t transition-all" :style="{ height: barHeight(d.posts) + '%' }" :title="`${d.posts} posts`"></div>
                <div class="flex-1 bg-teal-400 rounded-t transition-all" :style="{ height: barHeight(d.palettes) + '%' }" :title="`${d.palettes} palettes`"></div>
              </div>
              <p class="text-xs text-gray-400 text-center leading-tight" style="font-size:9px;">{{ d.date || d.month }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Three column: Sources + Staff + History -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Palette sources -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Palettes by Source</p>
          <div class="flex flex-col gap-3">
            <div v-for="src in sources" :key="src.label" class="flex items-center gap-3">
              <span>{{ src.icon }}</span>
              <div class="flex-1">
                <div class="flex justify-between mb-1">
                  <span class="text-xs text-gray-500 dark:text-gray-400">{{ src.label }}</span>
                  <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ loading ? '-' : src.value }}</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                  <div class="h-1.5 rounded-full" :class="src.barColor" :style="{ width: barWidth(src.value) }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Staff list -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Staff</p>
          <div class="flex flex-col gap-3">
            <div v-if="staffLoading" class="text-xs text-gray-400 text-center py-4">Loading...</div>
            <div v-for="s in staff" :key="s.id" class="flex items-center gap-3">
              <div class="relative">
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0">
                  <img v-if="s.avatar" :src="`http://localhost:8000/storage/${s.avatar}`" class="w-full h-full object-cover" />
                  <span v-else>{{ s.name?.charAt(0).toUpperCase() }}</span>
                </div>
                <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-gray-800" :class="s.role === 'superadmin' ? 'bg-red-500' : 'bg-blue-500'"></span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-700 dark:text-gray-200 truncate">{{ s.name }}</p>
                <p class="text-xs" :class="s.role === 'superadmin' ? 'text-red-400' : 'text-blue-400'">{{ s.role }}</p>
              </div>
              <p class="text-xs text-gray-400 shrink-0">{{ s.palettes_count }} palettes</p>
            </div>
          </div>
        </div>

        <!-- Yearly history -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
          <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Yearly History</p>
            <button v-if="isSuperAdmin()" @click="archiveYear" class="text-xs text-indigo-500 hover:text-indigo-700 transition">Archive {{ new Date().getFullYear() - 1 }}</button>
          </div>
          <div class="flex flex-col gap-3">
            <div v-if="!history.length" class="text-xs text-gray-400 text-center py-4">No history yet</div>
            <div v-for="h in history" :key="h.year" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-xl">
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ h.year }}</p>
              <div class="text-right">
                <p class="text-xs text-gray-500">👥 {{ h.data.total_users }} users</p>
                <p class="text-xs text-gray-500">🖼 {{ h.data.total_posts }} posts</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Quick Actions</p>
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-3">
          <button @click="$emit('navigate', 'AdminUsers')" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-sm font-medium hover:bg-indigo-100 transition">👥 Manage Users</button>
          <button @click="$emit('navigate', 'AdminPalettes')" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 text-sm font-medium hover:bg-orange-100 transition">🎨 Manage Palettes</button>
          <button @click="$emit('navigate', 'AdminReports')" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm font-medium hover:bg-red-100 transition">🚨 Reports</button>
          <button v-if="isSuperAdmin()" @click="$emit('navigate', 'AdminRoles')" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-sm font-medium hover:bg-purple-100 transition">⚡ Manage Roles</button>
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
const staffLoading = ref(true)
const data = ref(null)
const staff = ref([])
const history = ref([])
const chartData = ref([])
const chartMode = ref('daily')

const chartTabs = [
  { value: 'daily', label: '7 Days' },
  { value: 'monthly', label: '12 Months' },
]

onMounted(async () => {
  await Promise.all([loadStats(), loadStaff(), loadHistory(), loadChart()])
})

async function loadStats() {
  try {
    const { data: d } = await axios.get('/api/admin/stats')
    data.value = d
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

async function loadStaff() {
  try {
    const { data: d } = await axios.get('/api/admin/staff')
    staff.value = d
  } catch (e) { console.error(e) }
  finally { staffLoading.value = false }
}

async function loadHistory() {
  try {
    const { data: d } = await axios.get('/api/admin/stats/history')
    history.value = d
  } catch (e) { console.error(e) }
}

async function loadChart() {
  try {
    const url = chartMode.value === 'daily' ? '/api/admin/stats/daily' : '/api/admin/stats/monthly'
    const { data: d } = await axios.get(url)
    chartData.value = d
  } catch (e) { console.error(e) }
}

async function archiveYear() {
  try {
    await axios.post('/api/admin/stats/archive')
    await loadHistory()
  } catch (e) { console.error(e) }
}

const maxVal = computed(() => {
  if (!chartData.value.length) return 1
  return Math.max(...chartData.value.flatMap(d => [d.users || 0, d.posts || 0, d.palettes || 0]), 1)
})

function barHeight(val) { return Math.max(4, Math.round((val / maxVal.value) * 100)) }

const stats = computed(() => [
  { label: 'Total Users',    value: data.value?.total_users    ?? 0, color: 'text-indigo-500', sub: `${data.value?.new_users_this_week ?? 0} new this week` },
  { label: 'Total Palettes', value: data.value?.total_palettes ?? 0, color: 'text-orange-500', sub: 'across all users' },
  { label: 'New This Month', value: data.value?.new_users_this_month ?? 0, color: 'text-green-500', sub: 'new users' },
  { label: 'Total Posts',    value: data.value?.total_posts ?? 0, color: 'text-teal-500', sub: 'community posts' },
])

const sources = computed(() => [
  { label: 'Image',   icon: '🖼',  value: data.value?.by_source?.image   ?? 0, barColor: 'bg-orange-400' },
  { label: 'Keyword', icon: '🔍',  value: data.value?.by_source?.keyword ?? 0, barColor: 'bg-teal-400' },
  { label: 'Created', icon: '🎨',  value: data.value?.by_source?.created ?? 0, barColor: 'bg-indigo-400' },
])

const totalPalettes = computed(() => data.value?.total_palettes || 1)
function barWidth(val) { return Math.round((val / totalPalettes.value) * 100) + '%' }
</script>