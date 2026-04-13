<template>
  <div>
    <!-- Global Loading Screen -->
    <LoadingScreen :class="{ hidden: !isLoading }" />

    <!-- Navbar -->
    <TheNavbar @navigate="navigate" />

    <!-- Page Component -->
    <div v-show="!isLoading">
      <component :is="activeComp" />
    </div>
  </div>
</template>

<script setup>
import { shallowRef, markRaw, ref } from 'vue'
import TheNavbar from './components/Navbar.vue'
import LoadingScreen from './components/LoadingScreen.vue'

import Heroes from './components/Heroes.vue'
import KeywordColorPalette from './components/KeywordColorPalette.vue'
import ColorPalette from './components/ColorPalette.vue'
import CreatePalette from './components/CreatePalette.vue'
import SavePalette from './components/SavePalette.vue'

import './assets/style.css'

const isLoading = ref(false)

// Prevent Vue reactive warning
const compMap = {
  Heroes: markRaw(Heroes),
  KeywordColorPalette: markRaw(KeywordColorPalette),
  ColorPalette: markRaw(ColorPalette),
  CreatePalette: markRaw(CreatePalette),
  SavePalette: markRaw(SavePalette),
}

const activeComp = shallowRef(compMap.Heroes)

function navigate(name) {
  isLoading.value = true

  setTimeout(() => {
    activeComp.value = compMap[name] ?? compMap.Heroes
    isLoading.value = false
  }, 1000)
}
</script>