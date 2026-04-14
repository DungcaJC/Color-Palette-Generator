<template>
  <!-- Navbar.vue -->

  <div class="sticky top-0 z-50">
    <Disclosure as="nav" class="relative bg-[#0d1117] after:pointer-events-none after:absolute after:inset-x-0 after:bottom-0 after:h-px after:bg-white/10" v-slot="{ open }">
      <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
        <div class="relative flex h-16 items-center justify-between">
          <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
            <DisclosureButton class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-white/5 hover:text-white focus:outline-2 focus:-outline-offset-1 focus:outline-indigo-500">
              <span class="absolute -inset-0.5"></span>
              <span class="sr-only">Open main menu</span>
              <Bars3Icon v-if="!open" class="block size-6" aria-hidden="true" />
              <XMarkIcon v-else class="block size-6" aria-hidden="true" />
            </DisclosureButton>
          </div>

          <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
            <!-- Logo -->
            <div class="flex shrink-0 items-center cursor-pointer" @click="$emit('navigate', 'Heroes')">
              <img class="h-8 w-auto" :src="logo" alt="Palette Logo" />
            </div>

            <!-- Desktop nav -->
            <div class="hidden sm:ml-6 sm:block">
              <ul class="flex items-center gap-4">
                <li
                  @click="$emit('navigate', 'Heroes')"
                  class="text-gray-300 hover:bg-white/5 hover:text-white rounded-md px-3 py-2 text-sm font-medium cursor-pointer"
                >
                  Home
                </li>
                <li
                  v-for="item in navigation"
                  :key="item.name"
                  @click="$emit('navigate', item.comp)"
                  class="text-gray-300 hover:bg-white/5 hover:text-white rounded-md px-3 py-2 text-sm font-medium cursor-pointer"
                >
                  {{ item.name }}
                </li>
              </ul>
            </div>
          </div>

          <div class="absolute inset-y-0 right-0 flex items-center gap-2 pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">

            <!-- Guest -->
            <template v-if="!user">
              <button
                @click="$emit('navigate', 'Login')"
                class="text-sm text-gray-300 hover:text-white border border-white/15 hover:bg-white/5 rounded-md px-3 py-1.5 transition"
              >
                Log in
              </button>
              <button
                @click="$emit('navigate', 'Signup')"
                class="text-sm text-white bg-indigo-600 hover:bg-indigo-500 rounded-md px-3 py-1.5 transition"
              >
                Sign up
              </button>
            </template>

            <!-- Authenticated -->
            <template v-else>

              <!-- Bell -->
              <div class="relative" ref="bellRef">
                <button
                  @click="toggleNotif"
                  class="relative rounded-full p-1 text-gray-400 hover:text-white focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500"
                >
                  <BellIcon class="size-6" aria-hidden="true" />
                  <!-- Red dot -->
                  <span
                    v-if="unreadCount > 0"
                    class="absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-[#0d1117]"
                  ></span>
                </button>

                <!-- Notification Dropdown -->
                <transition
                  enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95"
                  enter-to-class="transform opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform opacity-100 scale-100"
                  leave-to-class="transform opacity-0 scale-95"
                >
                  <div
                    v-if="notifOpen"
                    class="absolute right-0 mt-2 w-80 origin-top-right rounded-xl bg-[#0d1117] border border-white/10 shadow-xl overflow-hidden z-50"
                  >
                    <!-- Header -->
                    <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                      <span class="text-sm font-medium text-white">Notifications</span>
                      <button
                        v-if="notifications.length"
                        @click="clearAll"
                        class="text-xs text-gray-400 hover:text-white transition"
                      >
                        Clear all
                      </button>
                    </div>

                    <!-- List -->
                    <div class="max-h-80 overflow-y-auto">
                      <p v-if="!notifications.length" class="text-xs text-gray-500 text-center py-8">
                        No notifications yet
                      </p>
                      <div
                        v-for="notif in notifications"
                        :key="notif.id"
                        @click="goToPalette(notif)"
                        class="flex items-start gap-3 px-4 py-3 cursor-pointer border-b border-white/5 transition hover:bg-white/5"
                        :class="!notif.read ? 'bg-indigo-950/40' : ''"
                      >
                        <!-- Color preview dots -->
                        <div class="flex gap-0.5 mt-1 shrink-0">
                          <div
                            v-for="(c, i) in notif.colors"
                            :key="i"
                            class="w-3 h-3 rounded-full border border-white/10"
                            :style="{ backgroundColor: c }"
                          ></div>
                        </div>

                        <div class="flex-1 min-w-0">
                          <!-- Unread dot -->
                          <div class="flex items-center gap-1.5">
                            <span
                              v-if="!notif.read"
                              class="w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0"
                            ></span>
                            <p class="text-sm text-white truncate font-medium">
                              {{ notif.name }}
                            </p>
                          </div>
                          <p class="text-xs text-gray-400 mt-0.5">has been saved</p>
                        </div>

                        <div class="text-right shrink-0">
                          <p class="text-xs text-gray-500">{{ formatDate(notif.date) }}</p>
                          <p class="text-xs text-gray-500">{{ formatTime(notif.date) }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </transition>
              </div>

              <!-- Profile dropdown -->
              <Menu as="div" class="relative ml-3">
                <MenuButton class="relative flex rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                  <span class="absolute -inset-1.5"></span>
                  <span class="sr-only">Open user menu</span>
                  <div class="size-8 rounded-full bg-indigo-600 outline -outline-offset-1 outline-white/10 flex items-center justify-center text-white font-bold text-sm select-none">
                    {{ userInitial }}
                  </div>
                </MenuButton>
                <transition
                  enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95"
                  enter-to-class="transform scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform scale-100"
                  leave-to-class="transform opacity-0 scale-95"
                >
                  <MenuItems class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-gray-800 py-1 outline -outline-offset-1 outline-white/10">
                    <div class="px-4 py-2 border-b border-white/10">
                      <p class="text-xs text-gray-400">Signed in as</p>
                      <p class="text-sm text-white font-medium truncate">{{ user?.name }}</p>
                    </div>
                    <MenuItem v-slot="{ active }">
                      <a href="#" :class="[active ? 'bg-white/5' : '', 'block px-4 py-2 text-sm text-gray-300']">Your Profile</a>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <a href="#" :class="[active ? 'bg-white/5' : '', 'block px-4 py-2 text-sm text-gray-300']">Settings</a>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <button
                        @click="$emit('logout')"
                        :class="[active ? 'bg-white/5' : '', 'block w-full text-left px-4 py-2 text-sm text-red-400']"
                      >
                        Sign out
                      </button>
                    </MenuItem>
                  </MenuItems>
                </transition>
              </Menu>

            </template>
          </div>
        </div>
      </div>

      <!-- Mobile menu -->
      <DisclosurePanel class="sm:hidden">
        <div class="space-y-1 px-2 pt-2 pb-3">
          <DisclosureButton as="button" @click="$emit('navigate', 'Heroes')"
            class="w-full text-left text-gray-300 hover:bg-white/5 hover:text-white block rounded-md px-3 py-2 text-base font-medium">
            Home
          </DisclosureButton>
          <DisclosureButton
            v-for="item in navigation" :key="item.name" as="button"
            @click="$emit('navigate', item.comp)"
            class="w-full text-left text-gray-300 hover:bg-white/5 hover:text-white block rounded-md px-3 py-2 text-base font-medium">
            {{ item.name }}
          </DisclosureButton>
          <template v-if="!user">
            <DisclosureButton as="button" @click="$emit('navigate', 'Login')"
              class="w-full text-left text-gray-300 hover:bg-white/5 hover:text-white block rounded-md px-3 py-2 text-base font-medium">
              Log in
            </DisclosureButton>
            <DisclosureButton as="button" @click="$emit('navigate', 'Signup')"
              class="w-full text-left text-white bg-indigo-600 hover:bg-indigo-500 block rounded-md px-3 py-2 text-base font-medium">
              Sign up
            </DisclosureButton>
          </template>
        </div>
      </DisclosurePanel>
    </Disclosure>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Bars3Icon, BellIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { useAuth } from '../composables/useAuth'
import { useNotifications } from '../composables/useNotifications'
import logo from '../assets/Logo-images/Palette-Logo.png'

const emit = defineEmits(['navigate', 'logout', 'goToPalette'])

const { user } = useAuth()
const { notifications, unreadCount, markAllRead, clearNotifications, syncUnread } = useNotifications()

const userInitial = computed(() => user.value?.name?.charAt(0).toUpperCase() || '?')

const notifOpen = ref(false)
const bellRef = ref(null)

function toggleNotif() {
  notifOpen.value = !notifOpen.value
  if (notifOpen.value) {
    markAllRead()
    syncUnread()
  }
}

function goToPalette(notif) {
  notifOpen.value = false
  emit('goToPalette', notif.paletteId)
}

function clearAll() {
  clearNotifications()
  syncUnread()
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

function formatTime(iso) {
  return new Date(iso).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

// Close on outside click
function onClickOutside(e) {
  if (bellRef.value && !bellRef.value.contains(e.target)) {
    notifOpen.value = false
  }
}
onMounted(() => document.addEventListener('mousedown', onClickOutside))
onBeforeUnmount(() => document.removeEventListener('mousedown', onClickOutside))

const navigation = [
  { name: 'Keyword',        comp: 'KeywordColorPalette' },
  { name: 'Generate Image', comp: 'ColorPalette'        },
  { name: 'Create Palette', comp: 'CreatePalette'       },
  { name: 'Save Palette',   comp: 'SavePalette'         },
]
</script>