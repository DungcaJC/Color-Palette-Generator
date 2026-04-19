<template>
  <!--KeywordColorPalette.vue-->

  <div class="min-h-screen bg-gray-50 p-8">

    <div class="max-w-3xl mx-auto mb-6">
      <input
        v-model="keyword"
        type="text"
        placeholder="e.g. 'aurora borealis' 'golden hour' 'deep ocean' 'cherry blossom'"
        class="w-full border-2 border-gray-800 rounded-full px-6 py-4 text-sm text-gray-500 focus:outline-none focus:border-gray-600 bg-white"
        @keyup.enter="search"
      />
    </div>

    <div class="flex items-center justify-center gap-3 mb-8 flex-wrap">
      <button
        @click="cycleColorCount"
        class="px-6 py-3 rounded-full bg-gray-100 text-gray-700 font-semibold text-sm hover:bg-gray-200 transition min-w-32 text-center"
      >
        {{ colorCount }} Color
      </button>

      <button
        @click="cyclePaletteCount"
        class="px-6 py-3 rounded-full bg-gray-100 text-gray-700 font-semibold text-sm hover:bg-gray-200 transition min-w-32 text-center"
      >
        {{ paletteCount }} Palette
      </button>

      <button
        @click="search"
        :disabled="loading"
        class="px-8 py-3 rounded-full text-white font-bold text-sm tracking-wider disabled:opacity-50 transition hover:opacity-90"
        style="background: linear-gradient(to right, #f97316, #f59e0b)"
      >
        {{ loading ? 'Searching...' : 'Generate' }}
      </button>

      <button
        @click="saveSelected"
        :disabled="selectedIds.size === 0 || saving"
        class="px-8 py-3 rounded-full text-white font-bold text-sm tracking-wider disabled:opacity-50 transition hover:opacity-90"
        style="background: linear-gradient(to right, #f59e0b, #fbbf24)"
      >
        {{ saving ? 'Saving...' : 'Save' }}
        <span v-if="selectedIds.size > 0" class="ml-1 bg-white/30 text-white text-xs px-2 py-0.5 rounded-full">
          {{ selectedIds.size }}
        </span>
      </button>
    </div>

    <p v-if="paletteCount >= 5" class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5 max-w-xl mx-auto text-center mb-6">
      ⚠️ Results are fetched from ColorMagic's database — more palettes means more variation from your keyword.
    </p>

    <p v-if="error" class="text-xs text-red-500 text-center mb-4">{{ error }}</p>
    <p v-if="savedMsg" class="text-xs text-green-600 text-center mb-4">{{ savedMsg }}</p>

    <p v-if="!loading && !palettes.length" class="text-gray-300 text-sm text-center mt-24">
      Type a keyword and hit Generate
    </p>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 max-w-7xl mx-auto">
      <div
        v-for="(palette, pi) in displayedPalettes"
        :key="palette.id"
        class="rounded-2xl overflow-hidden border-2 transition"
        :class="selectedIds.has(palette.id) ? 'border-orange-400 shadow-md' : 'border-orange-200'"
        style="background: #fdf3e3"
      >
        <div class="flex h-36">
          <div
            v-for="(color, ci) in palette.colors.slice(0, colorCount)"
            :key="ci"
            class="flex-1 flex items-end justify-center pb-1 group relative cursor-pointer"
            :style="{ backgroundColor: color }"
            @click="copyHex(color)"
            :title="'Click to copy ' + color"
          >
            <span class="text-xs font-mono text-white drop-shadow opacity-0 group-hover:opacity-100 transition-opacity select-none" style="font-size: 9px;">
              {{ color }}
            </span>
          </div>
        </div>

        <div class="flex px-1 pt-2 pb-1">
          <div v-for="(color, ci) in palette.colors.slice(0, colorCount)" :key="ci" class="flex-1 text-center">
            <span class="font-mono text-gray-600 cursor-pointer hover:text-gray-900 transition" style="font-size: 9px;" @click="copyHex(color)">
              {{ color }}
            </span>
          </div>
        </div>

        <div class="flex items-center justify-between px-3 py-2">
          <span class="text-xs text-gray-400 truncate max-w-24" :title="palette.text">
            {{ palette.text || 'Untitled' }}
          </span>
          <div class="flex items-center gap-1.5 cursor-pointer group" @click="toggleSelect(palette.id)">
            <span class="text-xs font-semibold text-gray-600 group-hover:text-gray-800 transition">Save</span>
            <div
              class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition"
              :class="selectedIds.has(palette.id) ? 'bg-orange-400 border-orange-400' : 'bg-gray-200 border-gray-300 group-hover:border-orange-300'"
            >
              <svg v-if="selectedIds.has(palette.id)" class="w-3 h-3" viewBox="0 0 12 12" fill="none">
                <path d="M2 6l3 3 5-5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <p v-if="copied" class="fixed bottom-6 left-1/2 -translate-x-1/2 text-xs bg-gray-800 text-white px-4 py-2 rounded-full shadow-lg">
      ✓ Copied {{ copied }}
    </p>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { usePaletteStore } from '../composables/usePaletteStore'
import { useNotifications } from '../composables/useNotifications'

const { save, generateId } = usePaletteStore()
const { addNotification } = useNotifications()

const keyword = ref('')
const palettes = ref([])
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const copied = ref('')
const savedMsg = ref('')
const selectedIds = ref(new Set())

const colorCounts = [3, 5, 7, 9]
const colorCountIndex = ref(1)
const colorCount = computed(() => colorCounts[colorCountIndex.value])
function cycleColorCount() { colorCountIndex.value = (colorCountIndex.value + 1) % colorCounts.length }

const paletteCounts = [4, 8, 12, 16]
const paletteCountIndex = ref(1)
const paletteCount = computed(() => paletteCounts[paletteCountIndex.value])
function cyclePaletteCount() { paletteCountIndex.value = (paletteCountIndex.value + 1) % paletteCounts.length }

const displayedPalettes = computed(() => palettes.value.slice(0, paletteCount.value))

async function search() {
  if (!keyword.value.trim()) { error.value = 'Please enter a keyword.'; return }
  loading.value = true
  error.value = ''
  selectedIds.value = new Set()
  try {
    const { data } = await axios.get(`/api/palette/search?q=${encodeURIComponent(keyword.value.trim())}`)
    palettes.value = data
    if (!data.length) error.value = 'No palettes found. Try a different keyword.'
  } catch (e) {
    error.value = 'Failed to fetch palettes. Make sure Laravel is running.'
  } finally {
    loading.value = false
  }
}

function toggleSelect(id) {
  const next = new Set(selectedIds.value)
  next.has(id) ? next.delete(id) : next.add(id)
  selectedIds.value = next
}

async function copyHex(hex) {
  await navigator.clipboard.writeText(hex)
  copied.value = hex
  setTimeout(() => copied.value = '', 2000)
}

async function saveSelected() {
  const toSave = palettes.value.filter(p => selectedIds.value.has(p.id))
  saving.value = true
  try {
    // ✅ await each save so we get the real server id back, then notify
    for (const p of toSave) {
      const palette = {
        id: generateId(),
        name: p.text || 'Keyword Palette',
        colors: p.colors.slice(0, colorCount.value),
        source: 'keyword',
        createdAt: new Date().toISOString(),
      }
      const result = await save(palette)
      addNotification(result)
    }
    savedMsg.value = `✓ ${toSave.length} palette${toSave.length > 1 ? 's' : ''} saved to collection!`
    selectedIds.value = new Set()
    setTimeout(() => savedMsg.value = '', 3000)
  } catch (e) {
    error.value = 'Failed to save. Please try again.'
  } finally {
    saving.value = false
  }
}
</script>

<style>
.keyword-palette-enter-active, .keyword-palette-leave-active { transition: all 0.3s ease; }
input { user-select: text; caret-color: black; }
</style>