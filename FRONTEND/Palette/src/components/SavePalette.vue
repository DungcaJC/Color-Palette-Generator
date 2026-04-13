<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-5xl mx-auto flex flex-col gap-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-800">Saved Palettes</h1>
          <p class="text-sm text-gray-400 mt-1">All your generated and created palettes.</p>
        </div>
        <button
          v-if="palettes.length"
          @click="clearAll"
          class="text-xs text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-4 py-2 rounded-full transition"
        >
          Clear all
        </button>
      </div>

      <!-- Filter tabs -->
      <div class="flex gap-2">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          @click="activeTab = tab.value"
          class="px-4 py-2 rounded-full text-sm font-medium border transition"
          :class="activeTab === tab.value
            ? 'bg-black text-white border-black'
            : 'bg-white text-gray-500 border-gray-200 hover:border-gray-400'"
        >
          {{ tab.label }} ({{ countBySource(tab.value) }})
        </button>
      </div>

      <!-- Empty state -->
      <div v-if="filtered.length === 0" class="flex flex-col items-center justify-center py-24 text-gray-300 gap-3">
        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75h16.5M3.75 14.25h16.5M9 3.75v16.5M15 3.75v16.5"/>
        </svg>
        <p class="text-sm">No saved palettes yet</p>
      </div>

      <!-- Palette list -->
      <div class="flex flex-col gap-4">
        <div
          v-for="palette in filtered"
          :key="palette.id"
          class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100"
        >
          <!-- Color bar -->
          <div class="flex h-20">
            <div
              v-for="(color, ci) in palette.colors"
              :key="ci"
              class="flex-1 flex items-end justify-center pb-2 cursor-pointer transition-all duration-300 ease-in-out relative group"
              :style="{ backgroundColor: color }"
              @click="copyHex(color)"
            >
              <span class="text-xs font-mono text-white drop-shadow opacity-0 group-hover:opacity-100 transition-opacity select-none">
                {{ color }}
              </span>
            </div>
          </div>

          <!-- Info row -->
          <div class="flex items-center justify-between px-4 py-3">
            <div>
              <p class="text-sm font-medium text-gray-700">{{ palette.name }}</p>
              <p class="text-xs text-gray-400 mt-0.5">
                <span
                  class="inline-block px-2 py-0.5 rounded-full text-xs mr-2"
                  :class="{
                    'bg-indigo-50 text-indigo-500': palette.source === 'created',
                    'bg-orange-50 text-orange-500': palette.source === 'image',
                    'bg-teal-50 text-teal-500':   palette.source === 'keyword',
                  }"
                >
                  {{ sourceLabel(palette.source) }}
                </span>
                {{ formatDate(palette.createdAt) }}
              </p>
            </div>

            <div class="flex gap-2">
              <button
                @click="exportPalette(palette)"
                class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 hover:border-gray-400 px-3 py-1.5 rounded-full transition"
              >
                Export
              </button>
              <button
                @click="deletePalette(palette.id)"
                class="text-xs text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-full transition"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <p v-if="copied" class="text-xs text-gray-400 text-center">✓ Copied {{ copied }}</p>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePaletteStore } from '../composables/usePaletteStore'

const { getAll, remove } = usePaletteStore()

const palettes = ref([])
const activeTab = ref('all')
const copied = ref('')

const tabs = [
  { label: 'All',     value: 'all'     },
  { label: 'Created', value: 'created' },
  { label: 'Image',   value: 'image'   },
  { label: 'Keyword', value: 'keyword' },
]

const filtered = computed(() =>
  activeTab.value === 'all'
    ? palettes.value
    : palettes.value.filter(p => p.source === activeTab.value)
)

function countBySource(source) {
  return source === 'all'
    ? palettes.value.length
    : palettes.value.filter(p => p.source === source).length
}

function sourceLabel(source) {
  return { created: '🎨 Created', image: '🖼 Image', keyword: '🔍 Keyword' }[source] || source
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function deletePalette(id) {
  remove(id)
  palettes.value = getAll()
}

function clearAll() {
  localStorage.removeItem('saved_palettes')
  palettes.value = []
}

async function copyHex(hex) {
  await navigator.clipboard.writeText(hex)
  copied.value = hex
  setTimeout(() => copied.value = '', 2000)
}

function exportPalette(palette) {
  const text = `${palette.name}\n${palette.colors.join(', ')}`
  const blob = new Blob([text], { type: 'text/plain' })
  const a = document.createElement('a')
  a.href = URL.createObjectURL(blob)
  a.download = `${palette.name.replace(/\s+/g, '_')}.txt`
  a.click()
}

onMounted(() => { palettes.value = getAll() })
</script>