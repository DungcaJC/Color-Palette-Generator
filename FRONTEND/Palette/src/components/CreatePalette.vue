<template>
  <!--CreatePalette.vue-->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center px-4 md:px-8 py-8 md:py-0 transition-colors duration-300">
    <div class="w-full max-w-3xl flex flex-col gap-4 md:gap-6">

      <div>
        <h1 class="text-xl md:text-2xl font-semibold text-gray-800 dark:text-white">Create Palette</h1>
        <p class="text-xs md:text-sm text-gray-400 mt-1">Pick your own colors and save your custom palette.</p>
      </div>

      <input
        v-model="paletteName"
        type="text"
        placeholder="Palette name (e.g. My Sunset)..."
        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl px-4 py-2 md:py-3 text-sm focus:outline-none focus:border-gray-400 transition"
      />

      <div class="flex flex-wrap gap-2 md:gap-3">
        <div
          v-for="(color, i) in colors"
          :key="i"
          class="relative group rounded-2xl overflow-hidden shadow-sm cursor-pointer"
          :style="{ width: isMobile ? '80px' : '100px', height: isMobile ? '80px' : '100px' }"
        >
          <div class="w-full h-full" :style="{ backgroundColor: color }"></div>
          <input
            type="color"
            :value="color"
            @input="colors[i] = $event.target.value"
            class="absolute inset-0 opacity-0 w-full h-full cursor-pointer"
          />
          <div class="absolute bottom-0 left-0 right-0 bg-black/30 text-white text-xs font-mono text-center py-0.5 md:py-1 select-none text-[10px] md:text-xs">
            {{ color }}
          </div>
          <button
            @click.stop="removeColor(i)"
            class="absolute top-1 right-1 bg-black/40 hover:bg-black/70 text-white rounded-full w-5 h-5 text-xs items-center justify-center hidden group-hover:flex transition"
          >
            ✕
          </button>
        </div>

        <button
          @click="addColor"
          class="rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 text-gray-400 dark:text-gray-500 hover:text-gray-500 transition flex flex-col items-center justify-center gap-1"
          :style="{ width: isMobile ? '80px' : '100px', height: isMobile ? '80px' : '100px' }"
        >
          <span class="text-xl md:text-2xl leading-none">+</span>
          <span class="text-xs">Add</span>
        </button>
      </div>

      <div v-if="colors.length" class="flex rounded-2xl overflow-hidden shadow-sm" :style="{ height: isMobile ? '12px' : '16px' }">
        <div v-for="(color, i) in colors" :key="i" class="flex-1 transition-all duration-300" :style="{ backgroundColor: color }"></div>
      </div>

      <div class="flex flex-col md:flex-row gap-3">
        <button
          @click="savePalette"
          :disabled="colors.length === 0 || saving"
          class="flex-1 py-3 md:py-4 rounded-full text-white font-bold text-xs md:text-sm tracking-widest disabled:opacity-40 transition hover:opacity-90"
          style="background: #eea62b"
        >
          {{ saving ? 'SAVING...' : 'SAVE PALETTE' }}
        </button>

        <button
          @click="resetAll"
          :disabled="colors.length === 0"
          class="px-4 md:px-6 py-3 md:py-4 rounded-full border border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 text-xs md:text-sm font-medium hover:border-gray-400 disabled:opacity-40 transition"
        >
          Clear all
        </button>
      </div>

      <p v-if="savedMsg" class="text-xs text-green-600 text-center">✓ {{ savedMsg }}</p>
      <p v-if="error" class="text-xs text-red-500 text-center">{{ error }}</p>
    </div>
  </div>

  <div>
    <Footer @navigate="$emit('navigate', $event)" />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { usePaletteStore } from '../composables/usePaletteStore'
import { useNotifications } from '../composables/useNotifications'
import Footer from './Footer.vue'
const emit = defineEmits(['navigate'])

const { save, generateId } = usePaletteStore()
const { addNotification } = useNotifications()

const colors = ref(['#4f46e5', '#f97316', '#f59e0b', '#10b981', '#ef4444'])
const paletteName = ref('')
const savedMsg = ref('')
const error = ref('')
const saving = ref(false)
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)

const isMobile = computed(() => windowWidth.value < 768)

onMounted(() => {
  if (typeof window !== 'undefined') {
    const handleResize = () => {
      windowWidth.value = window.innerWidth
    }
    window.addEventListener('resize', handleResize)
    return () => window.removeEventListener('resize', handleResize)
  }
})

function addColor() { colors.value.push('#aaaaaa') }
function removeColor(i) { colors.value.splice(i, 1) }
function resetAll() { colors.value = []; paletteName.value = '' }

async function savePalette() {
  if (colors.value.length === 0) { error.value = 'Add at least one color first.'; return }
  error.value = ''
  saving.value = true
  try {
    const palette = {
      id: generateId(),
      name: paletteName.value.trim() || 'Untitled Palette',
      colors: [...colors.value],
      source: 'created',
      createdAt: new Date().toISOString(),
    }
    const result = await save(palette)
    addNotification(result)
    savedMsg.value = `"${result.name}" saved!`
    setTimeout(() => savedMsg.value = '', 3000)
  } catch (e) {
    error.value = 'Failed to save. Please try again.'
  } finally {
    saving.value = false
  }
}
</script>