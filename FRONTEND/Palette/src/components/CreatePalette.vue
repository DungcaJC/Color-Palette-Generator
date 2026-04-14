<template>
  <!--CreatePalette.vue-->

  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-8">
    <div class="w-full max-w-3xl flex flex-col gap-6">

      <!-- Header -->
      <div>
        <h1 class="text-2xl font-semibold text-gray-800">Create Palette</h1>
        <p class="text-sm text-gray-400 mt-1">Pick your own colors and save your custom palette.</p>
      </div>

      <!-- Palette Name -->
      <input
        v-model="paletteName"
        type="text"
        placeholder="Palette name (e.g. My Sunset)..."
        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-400"
      />

      <!-- Color Swatches -->
      <div class="flex flex-wrap gap-3">
        <div
          v-for="(color, i) in colors"
          :key="i"
          class="relative group rounded-2xl overflow-hidden shadow-sm cursor-pointer"
          style="width: 100px; height: 100px;"
        >
          <!-- Color display -->
          <div class="w-full h-full" :style="{ backgroundColor: color }"></div>

          <!-- Color picker on click -->
          <input
            type="color"
            :value="color"
            @input="colors[i] = $event.target.value"
            class="absolute inset-0 opacity-0 w-full h-full cursor-pointer"
            title="Click to change color"
          />

          <!-- Hex label -->
          <div class="absolute bottom-0 left-0 right-0 bg-black/30 text-white text-xs font-mono text-center py-1 select-none">
            {{ color }}
          </div>

          <!-- Remove button -->
          <button
            @click.stop="removeColor(i)"
            class="absolute top-1 right-1 bg-black/40 hover:bg-black/70 text-white rounded-full w-5 h-5 text-xs items-center justify-center hidden group-hover:flex transition"
          >
            ✕
          </button>
        </div>

        <!-- Add Color Button -->
        <button
          @click="addColor"
          class="rounded-2xl border-2 border-dashed border-gray-300 hover:border-gray-400 text-gray-400 hover:text-gray-500 transition flex flex-col items-center justify-center gap-1"
          style="width: 100px; height: 100px;"
        >
          <span class="text-2xl leading-none">+</span>
          <span class="text-xs">Add color</span>
        </button>
      </div>

      <!-- Preview Bar -->
      <div v-if="colors.length" class="flex rounded-2xl overflow-hidden h-16 shadow-sm">
        <div
          v-for="(color, i) in colors"
          :key="i"
          class="flex-1 transition-all duration-300"
          :style="{ backgroundColor: color }"
        ></div>
      </div>

      <!-- Actions -->
      <div class="flex gap-3">
        <button
          @click="savePalette"
          :disabled="colors.length === 0"
          class="flex-1 py-4 rounded-full text-white font-bold text-sm tracking-widest disabled:opacity-40 transition hover:opacity-90"
          style="background: linear-gradient(to right, #4f46e5, #f97316)"
        >
          SAVE PALETTE
        </button>

        <button
          @click="clearAll"
          :disabled="colors.length === 0"
          class="px-6 py-4 rounded-full border border-gray-200 text-gray-500 text-sm font-medium hover:border-gray-400 disabled:opacity-40 transition"
        >
          Clear all
        </button>
      </div>

      <!-- Feedback -->
      <p v-if="saved" class="text-xs text-green-600 text-center">✓ {{ saved }}</p>
      <p v-if="error" class="text-xs text-red-500 text-center">{{ error }}</p>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { usePaletteStore } from '../composables/usePaletteStore'

const { save, generateId } = usePaletteStore()

const colors = ref(['#4f46e5', '#f97316', '#f59e0b', '#10b981', '#ef4444'])
const paletteName = ref('')
const saved = ref('')
const error = ref('')

function addColor() {
  colors.value.push('#aaaaaa')
}

function removeColor(i) {
  colors.value.splice(i, 1)
}

function clearAll() {
  colors.value = []
  paletteName.value = ''
}

function savePalette() {
  if (colors.value.length === 0) {
    error.value = 'Add at least one color first.'
    return
  }
  error.value = ''
  save({
    id: generateId(),
    name: paletteName.value.trim() || 'Untitled Palette',
    colors: [...colors.value],
    source: 'created',
    createdAt: new Date().toISOString(),
  })
  saved.value = `"${paletteName.value.trim() || 'Untitled Palette'}" saved!`
  setTimeout(() => saved.value = '', 3000)
}
</script>