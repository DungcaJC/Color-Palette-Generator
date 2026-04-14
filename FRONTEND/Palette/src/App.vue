<template>
  <div>
    <!-- Loading Screen -->
    <LoadingScreen v-if="isLoading" />

    <TheNavbar
      @navigate="navigate"
      @logout="handleLogout"
    />
    <component
      :is="activeComp"
      @navigate="navigate"
      @loggedIn="handleLogin"
    />
  </div>
</template>

<script setup>
import { shallowRef, markRaw, ref, onMounted } from 'vue'
import { useAuth } from './composables/useAuth'

import LoadingScreen       from './components/LoadingScreen.vue'
import TheNavbar           from './components/Navbar.vue'
import Heroes              from './components/Heroes.vue'
import Login               from './components/Login.vue'
import Signup              from './components/Signup.vue'
import KeywordColorPalette from './components/KeywordColorPalette.vue'
import ColorPalette        from './components/ColorPalette.vue'
import CreatePalette       from './components/CreatePalette.vue'
import SavePalette         from './components/SavePalette.vue'
import './assets/style.css'

const { isLoggedIn, logout } = useAuth()

const isLoading = ref(true)

onMounted(() => {
  setTimeout(() => {
    isLoading.value = false
  }, 1500)
})

const compMap = {
  Heroes:              markRaw(Heroes),
  Login:               markRaw(Login),
  Signup:              markRaw(Signup),
  KeywordColorPalette: markRaw(KeywordColorPalette),
  ColorPalette:        markRaw(ColorPalette),
  CreatePalette:       markRaw(CreatePalette),
  SavePalette:         markRaw(SavePalette),
}

const activeComp = shallowRef(compMap.Heroes)

function navigate(name) {
  activeComp.value = compMap[name] ?? compMap.Heroes
}

function handleLogin() {
  activeComp.value = compMap.Heroes
}

async function handleLogout() {
  await logout()
  activeComp.value = compMap.Heroes
}
</script>

<style>
  * {
    user-select: none;
    caret-color: transparent;
  }
</style>