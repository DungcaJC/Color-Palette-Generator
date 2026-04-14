<template>
  <!--ColorPalette.vue-->

  <div id="Generate-image" class="min-h-screen bg-gray-50 flex items-center justify-center p-8">
    <div class="flex gap-10 w-full">

      <!-- Left Panel -->
      <div class="flex flex-col gap-5 w-96 shrink-0">

        <!-- Upload Box -->
        <div
          class="bg-white rounded-3xl shadow-md aspect-square flex flex-col items-center justify-center cursor-pointer hover:shadow-xl transition overflow-hidden"
          @click="triggerUpload"
          @dragover.prevent
          @drop.prevent="onDrop"
        >
          <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileChange" />
          <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover" />
          <div v-else class="flex flex-col items-center gap-3 text-gray-300 select-none px-6 text-center">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V19a2 2 0 002 2h14a2 2 0 002-2v-2.5M16 10l-4-4m0 0L8 10m4-4v12"/>
            </svg>
            <span class="text-base font-medium text-gray-400">Click or drag & drop</span>
            <span class="text-xs text-gray-300">PNG, JPG, WEBP supported</span>
          </div>
        </div>

        <!-- Colors Per Palette -->
        <div>
          <p class="text-xs text-gray-400 font-medium mb-2 tracking-widest uppercase">Colors per palette</p>
          <div class="flex gap-2">
            <button
              v-for="n in [3, 5, 7, 9]"
              :key="n"
              @click="colorCount = n"
              class="flex-1 py-2.5 rounded-full border text-sm font-medium transition"
              :class="colorCount === n
                ? 'bg-black text-white border-black'
                : 'bg-white text-gray-500 border-gray-200 hover:border-gray-400'"
            >
              {{ n }}
            </button>
          </div>
          <p v-if="colorCount >= 7" class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5 mt-3 leading-relaxed">
            ⚠️ 7 colors or above will sometimes generate colors that don't appear in the picture. Try reducing the number of colors if you want more accurate results.
          </p>
        </div>

        <!-- Number of Palettes -->
        <div>
          <p class="text-xs text-gray-400 font-medium mb-2 tracking-widest uppercase">Number of palettes</p>
          <div class="flex gap-2">
            <button
              v-for="n in [1, 3, 5, 7]"
              :key="n"
              @click="paletteCount = n"
              class="flex-1 py-2.5 rounded-full border text-sm font-medium transition"
              :class="paletteCount === n
                ? 'bg-black text-white border-black'
                : 'bg-white text-gray-500 border-gray-200 hover:border-gray-400'"
            >
              {{ n }}
            </button>
          </div>
          <p v-if="paletteCount >= 5" class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5 mt-3 leading-relaxed">
            ⚠️ 5 palettes or above may produce results that vary further from your image.
          </p>
        </div>

        <!-- Generate Button -->
        <button
          @click="generate"
          :disabled="loading || !imgEl"
          class="w-full py-4 rounded-full text-white font-bold text-sm tracking-widest disabled:opacity-40 transition hover:opacity-90 mt-2"
          style="background: linear-gradient(to right, #4f46e5, #f97316)"
        >
          {{ loading ? 'GENERATING...' : 'GENERATE' }}
        </button>

        <!-- Save Selected Button -->
        <button
          @click="saveSelected"
          :disabled="selectedColors.length === 0"
          class="w-full py-4 rounded-full text-white font-bold text-sm tracking-widest disabled:opacity-40 transition hover:opacity-90"
          style="background: linear-gradient(to right, #f59e0b, #f97316)"
        >
          SAVE SELECTED
          <span v-if="selectedColors.length > 0" class="ml-2 bg-white/30 text-white text-xs px-2 py-0.5 rounded-full">
            {{ selectedColors.length }}
          </span>
        </button>

        <!-- Clear Selection -->
        <button
          v-if="selectedColors.length > 0"
          @click="clearSelection"
          class="text-xs text-gray-400 hover:text-gray-600 text-center transition"
        >
          Clear selection ({{ selectedColors.length }} selected)
        </button>

        <p v-if="savedMsg" class="text-xs text-green-600 text-center">{{ savedMsg }}</p>
        <p v-if="error" class="text-xs text-red-500 text-center">{{ error }}</p>
      </div>

      <!-- Right Panel -->
      <div class="flex-1 flex flex-col gap-5 justify-center">

        <p v-if="!palettes.length" class="text-gray-300 text-sm m-auto text-center">
          Upload an image and hit Generate
        </p>

        <div
          v-for="(palette, pi) in palettes"
          :key="pi"
          class="flex flex-col gap-2"
        >
          <!-- Palette label + select all row -->
          <div class="flex items-center justify-between px-1">
            <span class="text-xs text-gray-400 font-medium">Palette {{ pi + 1 }}</span>
            <button
              @click="toggleSelectAll(pi)"
              class="text-xs text-indigo-500 hover:text-indigo-700 transition"
            >
              {{ isAllSelected(pi) ? 'Deselect all' : 'Select all' }}
            </button>
          </div>

          <!-- Color bar with checkboxes -->
          <div class="flex rounded-2xl overflow-hidden h-40 w-full shadow-sm">
            <div
              v-for="(color, ci) in palette"
              :key="ci"
              class="relative flex flex-col items-center justify-between py-3 cursor-pointer transition-all duration-300 ease-in-out"
              :style="{ backgroundColor: color.css, flex: hoveredPalette === pi && hoveredColor === ci ? 3 : 1 }"
              :class="{ 'ring-4 ring-white ring-inset': isSelected(pi, ci) }"
              @mouseenter="hoveredPalette = pi; hoveredColor = ci"
              @mouseleave="hoveredPalette = null; hoveredColor = null"
            >
              <!-- Checkbox top -->
              <div
                @click="toggleColor(pi, ci, color.hex)"
                class="w-5 h-5 rounded-full border-2 border-white flex items-center justify-center transition cursor-pointer"
                :class="isSelected(pi, ci) ? 'bg-white' : 'bg-white/20'"
              >
                <svg v-if="isSelected(pi, ci)" class="w-3 h-3" viewBox="0 0 12 12" fill="none">
                  <path d="M2 6l3 3 5-5" stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>

              <!-- Hex label bottom — click to copy -->
              <span
                class="text-xs font-mono text-white drop-shadow transition-opacity duration-200 select-none cursor-pointer"
                :style="{ opacity: hoveredPalette === pi && hoveredColor === ci ? 1 : 0 }"
                @click="copyHex(color.hex)"
              >
                {{ color.hex }}
              </span>
            </div>
          </div>
        </div>

        <p v-if="copied" class="text-xs text-gray-400 text-center mt-1">✓ Copied {{ copied }}</p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useColormind } from '../composables/useColormind'
import { usePaletteStore } from '../composables/usePaletteStore'

const { loading, error, generateFromImage } = useColormind()
const { save, generateId } = usePaletteStore()

const fileInput = ref(null)
const previewUrl = ref('')
const imgEl = ref(null)
const colorCount = ref(5)
const paletteCount = ref(5)
const palettes = ref([])
const hoveredPalette = ref(null)
const hoveredColor = ref(null)
const copied = ref('')
const savedMsg = ref('')

// selected is a Set of "pi-ci" keys
const selection = ref(new Set())

const selectedColors = computed(() => {
  const result = []
  selection.value.forEach(key => {
    const [pi, ci] = key.split('-').map(Number)
    if (palettes.value[pi]?.[ci]) {
      result.push(palettes.value[pi][ci].hex)
    }
  })
  return result
})

function isSelected(pi, ci) {
  return selection.value.has(`${pi}-${ci}`)
}

function toggleColor(pi, ci) {
  const key = `${pi}-${ci}`
  const next = new Set(selection.value)
  next.has(key) ? next.delete(key) : next.add(key)
  selection.value = next
}

function isAllSelected(pi) {
  return palettes.value[pi]?.every((_, ci) => isSelected(pi, ci))
}

function toggleSelectAll(pi) {
  const next = new Set(selection.value)
  if (isAllSelected(pi)) {
    palettes.value[pi].forEach((_, ci) => next.delete(`${pi}-${ci}`))
  } else {
    palettes.value[pi].forEach((_, ci) => next.add(`${pi}-${ci}`))
  }
  selection.value = next
}

function clearSelection() {
  selection.value = new Set()
}

function triggerUpload() { fileInput.value.click() }

function loadFile(file) {
  if (!file || !file.type.startsWith('image/')) return
  const reader = new FileReader()
  reader.onload = e => {
    previewUrl.value = e.target.result
    const img = new Image()
    img.onload = () => { imgEl.value = img }
    img.src = e.target.result
  }
  reader.readAsDataURL(file)
}

function onFileChange(e) { loadFile(e.target.files[0]) }
function onDrop(e) { loadFile(e.dataTransfer.files[0]) }

async function generate() {
  if (!imgEl.value) return
  palettes.value = []
  selection.value = new Set()
  const results = await Promise.all(
    Array.from({ length: paletteCount.value }, (_, i) =>
      generateFromImage(imgEl.value, colorCount.value, i) // ← pass palette index
    )
  )
  palettes.value = results
}

async function copyHex(hex) {
  await navigator.clipboard.writeText(hex)
  copied.value = hex
  setTimeout(() => copied.value = '', 2000)
}

function saveSelected() {
  if (selectedColors.value.length === 0) return
  save({
    id: generateId(),
    name: `Image Palette`,
    colors: selectedColors.value,
    source: 'image',
    createdAt: new Date().toISOString(),
  })
  savedMsg.value = `✓ ${selectedColors.value.length} colors saved to collection!`
  clearSelection()
  setTimeout(() => savedMsg.value = '', 3000)
}
</script>