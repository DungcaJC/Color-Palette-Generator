<template>
  <!-- Navbar.vue -->
  <div class="sticky top-0 z-50">
    <Disclosure as="nav"
      class="relative bg-[#0d1117] after:pointer-events-none after:absolute after:inset-x-0 after:bottom-0 after:h-px after:bg-white/10"
      v-slot="{ open }">
      <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
        <div class="relative flex h-16 items-center justify-between">
          <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
            <DisclosureButton
              class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-white/5 hover:text-white focus:outline-2 focus:-outline-offset-1 focus:outline-indigo-500">
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
              <ul class="flex items-center gap-1">
                <li @click="$emit('navigate', 'Heroes')"
                  class="text-gray-300 hover:bg-white/5 hover:text-white rounded-md px-3 py-2 text-sm font-medium cursor-pointer">
                  Home
                </li>
                <li v-for="item in navigation" :key="item.name" @click="$emit('navigate', item.comp)"
                  class="text-gray-300 hover:bg-white/5 hover:text-white rounded-md px-3 py-2 text-sm font-medium cursor-pointer">
                  {{ item.name }}
                </li>

                <!-- Save dropdown -->
                <li class="relative" ref="saveMenuRef">
                  <button @click="saveMenuOpen = !saveMenuOpen"
                    class="flex items-center gap-1 text-gray-300 hover:bg-white/5 hover:text-white rounded-md px-3 py-2 text-sm font-medium cursor-pointer">
                    Save
                    <svg class="w-3 h-3 transition-transform" :class="saveMenuOpen ? 'rotate-180' : ''" fill="none"
                      stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  <transition enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                    <div v-if="saveMenuOpen"
                      class="absolute left-0 mt-2 w-44 origin-top-left rounded-xl bg-[#0d1117] border border-white/10 shadow-xl overflow-hidden z-50">
                      <button @click="navigateSave('SavePalette')"
                        class="w-full text-left px-4 py-2.5 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition flex items-center gap-2">
                        <span>🎨</span> My Palettes
                      </button>
                      <button @click="navigateSave('SavePost')"
                        class="w-full text-left px-4 py-2.5 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition flex items-center gap-2">
                        <span>🔖</span> Saved Posts
                      </button>
                    </div>
                  </transition>
                </li>

                <!-- Admin dropdown in navbar -->
                <li v-if="isAdmin()" class="relative" ref="adminMenuRef">
                  <button @click="adminMenuOpen = !adminMenuOpen"
                    class="flex items-center gap-1.5 text-sm font-medium px-3 py-2 rounded-md transition" :class="isSuperAdmin()
                      ? 'text-red-400 hover:bg-red-500/10'
                      : 'text-blue-400 hover:bg-blue-500/10'">
                    <span>{{ isSuperAdmin() ? '⚡ Super Admin' : '🛡 Admin' }}</span>
                    <svg class="w-3 h-3 transition-transform" :class="adminMenuOpen ? 'rotate-180' : ''" fill="none"
                      stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  <transition enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                    <div v-if="adminMenuOpen"
                      class="absolute left-0 mt-2 w-52 origin-top-left rounded-xl bg-[#0d1117] border border-white/10 shadow-xl overflow-hidden z-50">
                      <div class="px-4 py-2.5 border-b border-white/10">
                        <p class="text-xs text-gray-400 uppercase tracking-widest">Admin Panel</p>
                      </div>
                      <button @click="navigateAdmin('AdminDashboard')"
                        class="w-full text-left px-4 py-2.5 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition flex items-center gap-2">
                        <span>📊</span> Dashboard
                      </button>
                      <button @click="navigateAdmin('AdminUsers')"
                        class="w-full text-left px-4 py-2.5 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition flex items-center gap-2">
                        <span>👥</span> Manage Users
                      </button>
                      <button @click="navigateAdmin('AdminPalettes')"
                        class="w-full text-left px-4 py-2.5 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition flex items-center gap-2">
                        <span>🎨</span> Manage Palettes
                      </button>
                      <button @click="navigateAdmin('AdminReports')"
                        class="w-full text-left px-4 py-2.5 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition flex items-center gap-2">
                        <span>🚨</span> Reports
                      </button>
                      <button @click="navigateAdmin('AdminAppeals')"
                        class="w-full text-left px-4 py-2.5 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition flex items-center gap-2">
                        <span>📤</span> Appeals
                      </button>
                      <template v-if="isSuperAdmin()">
                        <div class="border-t border-white/10 mt-1"></div>
                        <button @click="navigateAdmin('AdminRoles')"
                          class="w-full text-left px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition flex items-center gap-2">
                          <span>⚡</span> Manage Roles
                        </button>
                      </template>
                    </div>
                  </transition>
                </li>
              </ul>
            </div>
          </div>

          <div class="absolute inset-y-0 right-0 flex items-center gap-2 pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">

            <!-- Guest -->
            <template v-if="!user">
              <button @click="$emit('navigate', 'Login')"
                class="text-sm text-gray-300 hover:text-white border border-white/15 hover:bg-white/5 rounded-md px-3 py-1.5 transition">
                Log in
              </button>
              <button @click="$emit('navigate', 'Signup')"
                class="text-sm text-white bg-indigo-600 hover:bg-indigo-500 rounded-md px-3 py-1.5 transition">
                Sign up
              </button>
            </template>

            <!-- Authenticated -->
            <template v-else>

              <!-- Bell -->
              <div class="relative" ref="bellRef">
                <button @click="toggleNotif"
                  class="relative rounded-full p-1 text-gray-400 hover:text-white focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500">
                  <BellIcon class="size-6" aria-hidden="true" />
                  <span v-if="unreadCount > 0"
                    class="absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-[#0d1117]"></span>
                </button>

                <!-- Notification Dropdown -->
                <transition enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100"
                  leave-to-class="transform opacity-0 scale-95">
                  <div v-if="notifOpen"
                    class="absolute right-0 mt-2 w-80 origin-top-right rounded-xl bg-[#0d1117] border border-white/10 shadow-xl overflow-hidden z-50">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                      <span class="text-sm font-medium text-white">Notifications</span>
                      <button v-if="notifications.length || serverNotifications.length" @click="clearAll"
                        class="text-xs text-gray-400 hover:text-white transition">
                        Clear all
                      </button>
                    </div>

                    <div class="max-h-80 overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                      <p v-if="!notifications.length && !serverNotifications.length"
                        class="text-xs text-gray-500 text-center py-8">
                        No notifications yet
                      </p>

                      <!-- Server notifications (warnings, role changes) -->
                      <div v-for="notif in serverNotifications" :key="`server-${notif.id}`"
                        @click="openServerNotif(notif)"
                        class="flex items-start gap-3 px-4 py-3 cursor-pointer border-b border-white/5 transition hover:bg-white/5"
                        :class="!notif.read_at ? 'bg-indigo-950/40' : ''">
                        <div class="shrink-0 mt-0.5">
                          <!-- Follow: show follower's avatar -->
                          <template v-if="notif.type === 'follow' && notif.data?.follower_avatar">
                            <div class="w-8 h-8 rounded-full overflow-hidden bg-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                              <img :src="`http://localhost:8000/storage/${notif.data.follower_avatar}`" class="w-full h-full object-cover" />
                            </div>
                          </template>
                          <template v-else-if="notif.type === 'follow' && !notif.data?.follower_avatar">
                            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                              {{ notif.data?.follower_name?.charAt(0).toUpperCase() || '?' }}
                            </div>
                          </template>
                          <!-- Like: show liker's avatar -->
                          <template v-else-if="notif.type === 'like' && notif.data?.liker_avatar">
                            <div class="w-8 h-8 rounded-full overflow-hidden bg-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                              <img :src="`http://localhost:8000/storage/${notif.data.liker_avatar}`" class="w-full h-full object-cover" />
                            </div>
                          </template>
                          <template v-else-if="notif.type === 'like'">
                            <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center text-lg">❤️</div>
                          </template>
                          <!-- Comment: show commenter's avatar -->
                          <template v-else-if="notif.type === 'comment' && notif.data?.commenter_avatar">
                            <div class="w-8 h-8 rounded-full overflow-hidden bg-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                              <img :src="`http://localhost:8000/storage/${notif.data.commenter_avatar}`" class="w-full h-full object-cover" />
                            </div>
                          </template>
                          <template v-else-if="notif.type === 'comment'">
                            <div class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center text-lg">💬</div>
                          </template>
                          <!-- Warning / role_change / general -->
                          <template v-else>
                            <span class="text-lg">{{ notif.type === 'warning' ? '⚠️' : '🎉' }}</span>
                          </template>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                          <div class="flex items-center gap-1.5">
                            <span v-if="!notif.read_at" class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                            <p class="text-sm text-white truncate font-medium">{{ notif.title }}</p>
                          </div>
                          <p class="text-xs text-gray-400 mt-0.5 truncate">{{ notif.message }}</p>
                        </div>
                        <p class="text-xs text-gray-500 shrink-0">{{ formatDate(notif.created_at) }}</p>
                      </div>

                      <!-- Local palette notifications -->
                      <div v-for="notif in notifications" :key="notif.id" @click="goToPalette(notif)"
                        class="flex items-start gap-3 px-4 py-3 cursor-pointer border-b border-white/5 transition hover:bg-white/5"
                        :class="!notif.read ? 'bg-indigo-950/40' : ''">
                        <div class="flex gap-0.5 mt-1 shrink-0">
                          <div v-for="(c, i) in notif.colors" :key="i"
                            class="w-3 h-3 rounded-full border border-white/10" :style="{ backgroundColor: c }"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                          <div class="flex items-center gap-1.5">
                            <span v-if="!notif.read" class="w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                            <p class="text-sm text-white truncate font-medium">{{ notif.name }}</p>
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
                <MenuButton
                  class="relative flex rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                  <span class="absolute -inset-1.5"></span>
                  <span class="sr-only">Open user menu</span>

                  <div class="relative">
                    <div
                      class="size-8 rounded-full bg-indigo-600 outline -outline-offset-1 outline-white/10 flex items-center justify-center text-white font-bold text-sm select-none overflow-hidden">
                      <img v-if="user?.avatar" :src="`http://localhost:8000/storage/${user.avatar}`"
                        class="w-full h-full object-cover" />
                      <span v-else>{{ userInitial }}</span>
                    </div>
                    <span class="absolute -top-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-[#0d1117]"
                      :class="roleDotClass"></span>
                  </div>
                </MenuButton>

                <transition enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95" enter-to-class="transform scale-100"
                  leave-active-class="transition ease-in duration-75" leave-from-class="transform scale-100"
                  leave-to-class="transform opacity-0 scale-95">
                  <MenuItems
                    class="absolute right-0 z-10 mt-2 w-52 origin-top-right rounded-xl bg-gray-800 py-1 outline -outline-offset-1 outline-white/10">
                    <div class="px-4 py-3 border-b border-white/10">
                      <p class="text-xs text-gray-400">Signed in as</p>
                      <p class="text-sm text-white font-medium truncate">{{ user?.name }}</p>
                      <div class="flex items-center gap-1.5 mt-1.5">
                        <span class="w-2 h-2 rounded-full" :class="roleDotClass"></span>
                        <span class="text-xs font-medium" :class="roleLabelClass">{{ roleLabel }}</span>
                      </div>
                    </div>

                    <MenuItem v-slot="{ active }">
                    <button @click="$emit('navigate', 'UserProfile')"
                      :class="[active ? 'bg-white/5' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-300']">
                      Your Profile
                    </button>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                    <button @click="$emit('navigate', 'UserSettings')"
                      :class="[active ? 'bg-white/5' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-300']">
                      Settings
                    </button>
                    </MenuItem>

                    <template v-if="isAdmin()">
                      <div class="border-t border-white/10 my-1"></div>
                      <MenuItem v-slot="{ active }">
                      <button @click="$emit('navigate', 'AdminDashboard')"
                        :class="[active ? 'bg-white/5' : '', 'block w-full text-left px-4 py-2 text-sm']"
                        :style="isSuperAdmin() ? 'color: #f87171' : 'color: #60a5fa'">
                        {{ isSuperAdmin() ? '⚡ Super Admin Panel' : '🛡 Admin Panel' }}
                      </button>
                      </MenuItem>
                    </template>

                    <div class="border-t border-white/10 my-1"></div>
                    <MenuItem v-slot="{ active }">
                    <button @click="$emit('logout')"
                      :class="[active ? 'bg-white/5' : '', 'block w-full text-left px-4 py-2 text-sm text-red-400']">
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
          <DisclosureButton v-for="item in navigation" :key="item.name" as="button"
            @click="$emit('navigate', item.comp)"
            class="w-full text-left text-gray-300 hover:bg-white/5 hover:text-white block rounded-md px-3 py-2 text-base font-medium">
            {{ item.name }}
          </DisclosureButton>

          <template v-if="isAdmin()">
            <div class="border-t border-white/10 my-1"></div>
            <DisclosureButton as="button" @click="$emit('navigate', 'AdminDashboard')"
              class="w-full text-left block rounded-md px-3 py-2 text-base font-medium"
              :class="isSuperAdmin() ? 'text-red-400' : 'text-blue-400'">
              {{ isSuperAdmin() ? '⚡ Super Admin' : '🛡 Admin Panel' }}
            </DisclosureButton>
          </template>

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

  <!-- Server Notification Modal (Warning / Role Change) -->
  <div v-if="activeServerNotif" class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center px-4"
    @click.self="activeServerNotif = null">
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6 flex flex-col gap-4 max-h-[85vh] overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">

      <!-- Warning notification modal — replace existing -->
      <template v-if="activeServerNotif?.type === 'warning'">
        <div class="flex items-center gap-3">
          <span class="text-3xl">⚠️</span>
          <div>
            <h2 class="text-base font-semibold text-gray-800 dark:text-white">{{ activeServerNotif.title }}</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ formatDateLong(activeServerNotif.created_at) }}</p>
          </div>
        </div>

        <div
          class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 flex flex-col gap-2">
          <p class="text-sm font-medium text-amber-700 dark:text-amber-400">Reason: {{
            topicLabel(activeServerNotif.data?.report_category) }}</p>
          <p class="text-sm text-amber-600" style="white-space: pre-wrap; word-break: break-word;">
            {{ activeServerNotif.data?.auto_caption }}
          </p>
          <p v-if="activeServerNotif.data?.admin_text"
            class="text-sm text-gray-600 dark:text-gray-300 italic border-t border-amber-200 pt-2 mt-1">"{{
              activeServerNotif.data.admin_text }}"</p>
        </div>

        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-3">
          <p class="text-xs text-red-600 dark:text-red-400">
            ⏰ Account will be banned on <strong>{{ formatDateLong(activeServerNotif.data?.expires_at) }}</strong> ({{
              activeServerNotif.data?.expires_days }} day{{ activeServerNotif.data?.expires_days > 1 ? 's' : '' }}).
          </p>
        </div>

        <!-- Show post if attached -->
        <div v-if="activeServerNotif.data?.post_id" class="text-xs text-gray-400">Post ID: {{
          activeServerNotif.data.post_id }}</div>

        <!-- Appeal section -->
        <div v-if="!appealSubmitted && !existingAppeal">
          <button @click="showAppeal = !showAppeal"
            class="w-full text-left text-xs text-indigo-500 hover:text-indigo-700 transition flex items-center gap-1">
            <svg class="w-3.5 h-3.5 transition-transform" :class="showAppeal ? 'rotate-90' : ''" fill="none"
              stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            Submit an Appeal
          </button>

          <div v-if="showAppeal" class="mt-3 flex flex-col gap-3 animate-fade-in-up">
            <p class="text-xs text-gray-400">Explain why this warning should be reconsidered and provide any proof.</p>

            <!-- Multiple image upload -->
            <div>
              <p class="text-xs text-gray-400 mb-1">Proof images (up to 5, optional)</p>
              <div class="flex gap-2 flex-wrap">
                <div v-for="(img, i) in appealImages" :key="i"
                  class="relative w-14 h-14 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                  <img :src="img.preview" class="w-full h-full object-cover" />
                  <button @click="removeAppealImage(i)"
                    class="absolute top-0.5 right-0.5 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-xs">✕</button>
                </div>
                <label v-if="appealImages.length < 5"
                  class="w-14 h-14 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center cursor-pointer hover:border-indigo-400 transition">
                  <span class="text-xl text-gray-400">+</span>
                  <input type="file" accept="image/*" class="hidden" @change="addAppealImage" style="background-color: #f97316; cursor: pointer;"/>
                </label>
              </div>
            </div>

            <textarea v-model="appealText" placeholder="Explain your situation and write your apology..." rows="3"
              class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none resize-none transition"
              maxlength="1000"
              style="white-space: pre-wrap; word-break: break-word;"
            ></textarea>
            <p class="text-xs text-gray-400 text-right">{{ appealText.length }}/1000</p>

            <p v-if="appealMsg" class="text-xs" :class="appealMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{
              appealMsg }}</p>

            <button @click="submitAppeal" :disabled="!appealText.trim() || submittingAppeal"
              class="w-full py-2.5 rounded-xl text-white text-sm font-medium disabled:opacity-40 transition"
              style="background: linear-gradient(to right, #4f46e5, #f97316)">
              {{ submittingAppeal ? 'Submitting...' : '📤 Submit Appeal' }}
            </button>
          </div>
        </div>

        <div v-else-if="appealSubmitted || existingAppeal"
          class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-3">
          <p class="text-xs text-green-600 dark:text-green-400">✓ Appeal submitted. We'll review it shortly.</p>
        </div>

        <button @click="activeServerNotif = null; showAppeal = false; appealText = ''; appealImages = []"
          class="w-full py-2.5 rounded-xl bg-gray-800 text-white text-sm font-medium hover:bg-gray-700 transition">
          Close
        </button>
      </template>

      <!-- Role change notification -->
      <template v-else-if="activeServerNotif.type === 'role_change'">
        <div class="flex items-center gap-3">
          <span class="text-3xl">🎉</span>
          <h2 class="text-base font-semibold text-gray-800 dark:text-white">{{ activeServerNotif.title }}</h2>
        </div>
        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-4">
          <p class="text-sm text-indigo-700 dark:text-indigo-400">{{ activeServerNotif.message }}</p>
        </div>
      </template>

      <!-- General notification (appeal accepted, etc.) -->
      <template v-else-if="activeServerNotif.type === 'general'">
        <div class="flex items-center gap-3">
          <span class="text-3xl">✅</span>
          <div>
            <h2 class="text-base font-semibold text-gray-800 dark:text-white">{{ activeServerNotif.title }}</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ formatDateLong(activeServerNotif.created_at) }}</p>
          </div>
        </div>
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
          <p class="text-sm text-green-700 dark:text-green-400">{{ activeServerNotif.message }}</p>
          <p v-if="activeServerNotif.data?.admin_response" class="text-xs text-gray-500 dark:text-gray-400 mt-2 italic">"{{ activeServerNotif.data.admin_response }}"</p>
        </div>
      </template>

      <button @click="activeServerNotif = null"
        class="w-full py-2.5 rounded-xl bg-gray-800 text-white text-sm font-medium hover:bg-gray-700 transition">Got it</button>

      <button @click="activeServerNotif = null"
        class="w-full py-2.5 rounded-xl bg-gray-800 text-white text-sm font-medium hover:bg-gray-700 transition">Got
        it</button>
    </div>
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

const { user, isAdmin, isSuperAdmin } = useAuth()
const { notifications, serverNotifications, unreadCount, markAllRead, markServerRead, clearNotifications, loadServerNotifications } = useNotifications()

const userInitial = computed(() => user.value?.name?.charAt(0).toUpperCase() || '?')

// Role dot color on avatar
const roleDotClass = computed(() => {
  if (isSuperAdmin()) return 'bg-red-500'
  if (isAdmin()) return 'bg-blue-500'
  return 'bg-green-500'
})

// Role label color in dropdown
const roleLabelClass = computed(() => {
  if (isSuperAdmin()) return 'text-red-400'
  if (isAdmin()) return 'text-blue-400'
  return 'text-green-400'
})

// Role text
const roleLabel = computed(() => {
  if (isSuperAdmin()) return 'Super Admin'
  if (isAdmin()) return 'Admin'
  return 'User'
})

// Admin dropdown in navbar
const adminMenuOpen = ref(false)
const adminMenuRef = ref(null)

function navigateAdmin(comp) {
  adminMenuOpen.value = false
  emit('navigate', comp)
}

const saveMenuOpen = ref(false)
const saveMenuRef = ref(null)

function navigateSave(comp) {
  saveMenuOpen.value = false
  emit('navigate', comp)
}

// Notification
const notifOpen = ref(false)
const bellRef = ref(null)

// Server notification modal
const activeServerNotif = ref(null)

function toggleNotif() {
  notifOpen.value = !notifOpen.value
  if (notifOpen.value) markAllRead()
}

function openServerNotif(notif) {
  markServerRead(notif.id)
  notifOpen.value = false
  if (['warning', 'role_change', 'general'].includes(notif.type)) {
    activeServerNotif.value = notif
  }
}

function goToPalette(notif) {
  notifOpen.value = false
  emit('goToPalette', notif.paletteId)
}

function clearAll() { clearNotifications() }

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

function formatTime(iso) {
  return new Date(iso).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

function formatDateLong(iso) {
  if (!iso) return 'N/A'
  return new Date(iso).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
}

function topicLabel(topic) {
  const map = {
    spam: '📢 Spam',
    inappropriate: '🚫 Inappropriate',
    harassment: '😡 Harassment',
    copyright: '©️ Copyright',
    other: '❓ Other'
  }
  return map[topic] || topic
}

// Close dropdowns on outside click
function onClickOutside(e) {
  if (bellRef.value && !bellRef.value.contains(e.target)) notifOpen.value = false
  if (adminMenuRef.value && !adminMenuRef.value.contains(e.target)) adminMenuOpen.value = false
  if (saveMenuRef.value && !saveMenuRef.value.contains(e.target)) saveMenuOpen.value = false
}

onMounted(() => {
  loadServerNotifications()
  document.addEventListener('mousedown', onClickOutside)
})

onBeforeUnmount(() => document.removeEventListener('mousedown', onClickOutside))

const navigation = [
  { name: 'Keyword', comp: 'KeywordColorPalette' },
  { name: 'Generate Image', comp: 'ColorPalette' },
  { name: 'Create Palette', comp: 'CreatePalette' },
  { name: 'Community', comp: 'Community' },
]

const showAppeal = ref(false)
const appealText = ref('')
const appealImages = ref([])
const appealMsg = ref('')
const submittingAppeal = ref(false)
const appealSubmitted = ref(false)
const existingAppeal = ref(false)

function addAppealImage(e) {
  const file = e.target.files[0]
  if (!file || appealImages.value.length >= 5) return
  const reader = new FileReader()
  reader.onload = ev => { appealImages.value.push({ file, preview: ev.target.result }) }
  reader.readAsDataURL(file)
}

function removeAppealImage(i) { appealImages.value.splice(i, 1) }

async function submitAppeal() {
  if (!appealText.value.trim()) return
  submittingAppeal.value = true
  appealMsg.value = ''
  try {
    const warningId = activeServerNotif.value?.data?.warning_id
    if (!warningId) { appealMsg.value = 'Warning ID not found.'; return }

    const formData = new FormData()
    formData.append('apology_text', appealText.value)
    // Send images as an array
    appealImages.value.forEach((img) => formData.append('images[]', img.file))

    await axios.post(`/api/warnings/${warningId}/appeal`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    appealSubmitted.value = true
    appealMsg.value = '✓ Appeal submitted!'
    showAppeal.value = false
    const { removeServerNotification } = useNotifications()
    if (activeServerNotif.value?.id) {
      removeServerNotification(activeServerNotif.value.id)
    }
  } catch (e) {
    console.error('Appeal error:', e)
    appealMsg.value = e?.response?.data?.message || 'Failed to submit appeal.'
  } finally {
    submittingAppeal.value = false
  }
}
</script>