<template>
  <!-- AdminPalettes.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <div class="bg-[#0d1117] pt-10 pb-20 px-8">
      <div class="max-w-6xl mx-auto">
        <div class="flex items-center gap-3 mb-1">
          <span class="text-2xl">🎨</span>
          <h1 class="text-white text-2xl font-semibold">Manage Palettes</h1>
        </div>
        <p class="text-gray-400 text-sm ml-9">View and delete palettes from all users</p>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-8 -mt-12 pb-16 flex flex-col gap-4">

      <!-- Filter tabs -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex gap-2 flex-wrap">
        <button
          v-for="tab in tabs" :key="tab.value"
          @click="activeTab = tab.value; fetchPalettes()"
          class="px-4 py-2 rounded-full text-sm font-medium border transition"
          :class="activeTab === tab.value
            ? 'bg-black dark:bg-white text-white dark:text-black border-black dark:border-white'
            : 'bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:border-gray-400'"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-24">
        <div class="w-6 h-6 border-2 border-gray-300 border-t-orange-500 rounded-full animate-spin"></div>
      </div>

      <!-- Palette grid -->
      <div v-else class="flex flex-col gap-3">
        <div
          v-for="palette in palettes" :key="palette.id"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
        >
          <!-- Color bar -->
          <div class="flex h-16">
            <div
              v-for="(color, ci) in palette.colors" :key="ci"
              class="flex-1"
              :style="{ backgroundColor: color }"
            ></div>
          </div>

          <!-- Info row -->
          <div class="flex items-center justify-between px-4 py-3">
            <div>
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ palette.name }}</p>
              <p class="text-xs text-gray-400 mt-0.5">
                <span
                  class="inline-block px-2 py-0.5 rounded-full text-xs mr-2"
                  :class="{
                    'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-500': palette.source === 'created',
                    'bg-orange-50 dark:bg-orange-900/40 text-orange-500': palette.source === 'image',
                    'bg-teal-50 dark:bg-teal-900/40 text-teal-500': palette.source === 'keyword',
                  }"
                >
                  {{ sourceLabel(palette.source) }}
                </span>
                by <span class="text-gray-500 dark:text-gray-300">{{ palette.user?.name || 'Unknown' }}</span>
                · {{ formatDate(palette.created_at) }}
              </p>
            </div>
            <button
              @click="confirmDelete(palette)"
              class="text-xs text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-full transition"
            >
              Delete
            </button>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination" class="flex items-center justify-between px-2">
          <p class="text-xs text-gray-400">
            Showing {{ pagination.from }}–{{ pagination.to }} of {{ pagination.total }}
          </p>
          <div class="flex gap-2">
            <button
              @click="page--; fetchPalettes()"
              :disabled="page <= 1"
              class="text-xs px-3 py-1.5 rounded-full border border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 disabled:opacity-40 hover:border-gray-400 transition"
            >
              ← Prev
            </button>
            <button
              @click="page++; fetchPalettes()"
              :disabled="page >= pagination.last_page"
              class="text-xs px-3 py-1.5 rounded-full border border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 disabled:opacity-40 hover:border-gray-400 transition"
            >
              Next →
            </button>
          </div>
        </div>

        <p v-if="palettes.length === 0" class="text-center text-gray-400 text-sm py-12">No palettes found</p>
      </div>

      <p v-if="msg" class="text-xs text-center" :class="msg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ msg }}</p>
    </div>

    <!-- Confirm Delete Modal -->
    <div v-if="deleteTarget" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center px-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-sm">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-2">Delete palette?</h2>
        <div class="flex h-10 rounded-xl overflow-hidden mb-4">
          <div v-for="(c, i) in deleteTarget.colors" :key="i" class="flex-1" :style="{ backgroundColor: c }"></div>
        </div>
        <p class="text-sm text-gray-400 mb-6">
          Delete <span class="font-medium text-gray-700 dark:text-gray-200">{{ deleteTarget.name }}</span> by {{ deleteTarget.user?.name }}?
        </p>
        <div class="flex gap-3">
          <button @click="deleteTarget = null" class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 hover:border-gray-400 transition">
            Cancel
          </button>
          <button @click="deletePalette" class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition">
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

const palettes = ref([])
const loading = ref(true)
const msg = ref('')
const deleteTarget = ref(null)
const activeTab = ref('all')
const page = ref(1)
const pagination = ref(null)

const tabs = [
  { label: 'All',     value: 'all'     },
  { label: 'Image',   value: 'image'   },
  { label: 'Keyword', value: 'keyword' },
  { label: 'Created', value: 'created' },
]

onMounted(fetchPalettes)

async function fetchPalettes() {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: page.value })
    if (activeTab.value !== 'all') params.set('source', activeTab.value)
    const { data } = await axios.get(`/api/admin/palettes?${params}`)
    palettes.value = data.data
    pagination.value = {
      from: data.from,
      to: data.to,
      total: data.total,
      last_page: data.last_page,
    }
  } catch (e) {
    msg.value = 'Failed to load palettes.'
  } finally {
    loading.value = false
  }
}

function confirmDelete(palette) {
  deleteTarget.value = palette
}

async function deletePalette() {
  try {
    await axios.delete(`/api/admin/palettes/${deleteTarget.value.id}`)
    palettes.value = palettes.value.filter(p => p.id !== deleteTarget.value.id)
    msg.value = '✓ Palette deleted.'
    deleteTarget.value = null
    setTimeout(() => msg.value = '', 3000)
  } catch (e) {
    msg.value = 'Failed to delete.'
  }
}

function sourceLabel(source) {
  return { created: '🎨 Created', image: '🖼 Image', keyword: '🔍 Keyword' }[source] || source
}

function formatDate(iso) {
  if (!iso) return '-'
  return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>