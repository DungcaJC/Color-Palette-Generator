<template>

  <!-- App.vue -->

  <div>
    <Transition name="fade">
      <LoadingScreen v-if="isLoading" />
    </Transition>

    <TheNavbar
      v-if="isLoggedIn()"
      @navigate="navigate"
      @logout="handleLogout"
      @goToPalette="handleGoToPalette"
    />

    <component
      :is="activeComp"
      :scrollToId="scrollToId"
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
import UserProfile         from './components/UserProfile.vue'
import UserSettings        from './components/UserSettings.vue'
import AdminDashboard      from './components/AdminDashboard.vue'
import AdminUsers          from './components/AdminUsers.vue'
import AdminPalettes       from './components/AdminPalettes.vue'
import AdminRoles          from './components/AdminRoles.vue'
import Community           from './components/Community.vue'
import AdminReports        from './components/AdminReports.vue'
import SavePost            from './components/SavePost.vue'
import './assets/style.css'

const { isLoggedIn, logout, refreshUser } = useAuth()

const isLoading = ref(true)
const scrollToId = ref(null)

onMounted(async () => {
  const prefs = JSON.parse(localStorage.getItem('user_preferences') || '{}')
  if (prefs.darkMode) document.documentElement.classList.add('dark')

  if (isLoggedIn()) await refreshUser()
  
  setTimeout(() => { isLoading.value = false }, 1500)
})

const compMap = {
  Heroes:              markRaw(Heroes),
  Login:               markRaw(Login),
  Signup:              markRaw(Signup),
  KeywordColorPalette: markRaw(KeywordColorPalette),
  ColorPalette:        markRaw(ColorPalette),
  CreatePalette:       markRaw(CreatePalette),
  SavePalette:         markRaw(SavePalette),
  UserProfile:         markRaw(UserProfile),
  UserSettings:        markRaw(UserSettings),
  AdminDashboard:      markRaw(AdminDashboard),
  AdminUsers:          markRaw(AdminUsers),
  AdminPalettes:       markRaw(AdminPalettes),
  AdminRoles:          markRaw(AdminRoles),
  Community:           markRaw(Community),
  AdminReports:        markRaw(AdminReports),
  SavePost:            markRaw(SavePost),
}

// If already logged in show Heroes, otherwise show Login
const activeComp = shallowRef(isLoggedIn() ? compMap.Heroes : compMap.Login)

function showLoading(callback) {
  isLoading.value = true
  setTimeout(() => {
    callback()
    setTimeout(() => { isLoading.value = false }, 600)
  }, 600)
}

function navigate(name) {
  scrollToId.value = null
  showLoading(() => { activeComp.value = compMap[name] ?? compMap.Heroes })
}

function handleLogin() {
  showLoading(() => { activeComp.value = compMap.Heroes })
}

async function handleLogout() {
  try {
    await logout()
  } catch (e) {
    // force logout even if API fails
  }
  showLoading(() => { activeComp.value = compMap.Login })
}

function handleGoToPalette(paletteId) {
  scrollToId.value = paletteId
  showLoading(() => { activeComp.value = compMap.SavePalette })
}
</script>

<style>
* {
  user-select: none;
}

input, textarea, select, [contenteditable] {
  user-select: text;
  caret-color: auto;
}

:global(.dark) input,
:global(.dark) textarea,
:global(.dark) select {
  color: #ffffff;
  caret-color: #ffffff;
}

:global(.dark) input::placeholder,
:global(.dark) textarea::placeholder {
  color: #aaaaaa;
}

.fade-leave-active { transition: opacity 0.6s ease; }
.fade-leave-to { opacity: 0; }
</style>