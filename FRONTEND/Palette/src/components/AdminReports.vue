<template>
  <!-- AdminReports.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <div class="bg-[#0d1117] pt-8 md:pt-10 pb-16 md:pb-20 px-4 md:px-8">
      <div class="max-w-6xl mx-auto">
        <!-- Quick Actions Dropdown (Mobile & Desktop) -->
        <div class="mb-4 relative" ref="quickActionsRef">
          <button
            @click="quickActionsOpen = !quickActionsOpen"
            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/10 text-white text-sm font-medium border border-white/20 hover:bg-white/20 transition"
          >
            <span>⚡ Quick Actions</span>
            <svg class="w-4 h-4 transition-transform" :class="quickActionsOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          
          <div
            v-if="quickActionsOpen"
            class="absolute top-full mt-2 w-48 bg-gray-800 border border-white/20 rounded-lg shadow-xl overflow-hidden z-50"
          >
            <button
              @click="navigateAdmin('AdminUsers'); quickActionsOpen = false"
              class="w-full text-left px-4 py-2.5 text-white text-sm hover:bg-white/10 transition flex items-center gap-2 border-b border-white/10"
            >
              <span>👥</span> Manage Users
            </button>
            <button
              @click="navigateAdmin('AdminPalettes'); quickActionsOpen = false"
              class="w-full text-left px-4 py-2.5 text-white text-sm hover:bg-white/10 transition flex items-center gap-2 border-b border-white/10"
            >
              <span>🎨</span> Manage Palettes
            </button>
            <button
              @click="navigateAdmin('AdminReports'); quickActionsOpen = false"
              class="w-full text-left px-4 py-2.5 text-white text-sm hover:bg-white/10 transition flex items-center gap-2 border-b border-white/10"
            >
              <span>🚨</span> Reports
            </button>
            <button
              @click="navigateAdmin('AdminAppeals'); quickActionsOpen = false"
              class="w-full text-left px-4 py-2.5 text-white text-sm hover:bg-white/10 transition flex items-center gap-2 border-b border-white/10"
            >
              <span>📤</span> Appeals
            </button>
            <button
              @click="navigateAdmin('AdminRoles'); quickActionsOpen = false"
              class="w-full text-left px-4 py-2.5 text-white text-sm hover:bg-white/10 transition flex items-center gap-2"
            >
              <span>⚡</span> Manage Roles
            </button>
          </div>
        </div>

        <div class="flex items-center gap-3 mb-1">
          <span class="text-2xl">🚨</span>
          <h1 class="text-white text-2xl font-semibold">Reports</h1>
        </div>
        <p class="text-gray-400 text-sm ml-9">Review reported posts and comments</p>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-8 -mt-12 pb-16 flex flex-col gap-4">

      <!-- Filter bar -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 grid grid-cols-1 sm:grid-cols-2 gap-3 md:flex md:items-center md:gap-4">

        <!-- Search -->
        <input
          v-model="search"
          type="text"
          placeholder="Search by reporter or content..."
          class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-400 transition min-w-48"
          @input="fetchReports"
        />

        <div class="relative" ref="sortDropRef">
          <button @click="sortDropOpen = !sortDropOpen"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-sm text-gray-600 dark:text-gray-300 hover:border-indigo-400 transition min-w-36">
            <span>{{ sortLabels[sortBy] }}</span>
            <svg class="w-4 h-4 ml-auto transition-transform" :class="sortDropOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div v-if="sortDropOpen" class="absolute z-10 mt-1 w-44 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl overflow-hidden">
            <button v-for="opt in sortOptions" :key="opt.value" @click="sortBy = opt.value; sortDropOpen = false; fetchReports()"
              class="w-full text-left px-4 py-2.5 text-sm transition flex items-center gap-2"
              :class="sortBy === opt.value ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
            ><span>{{ opt.icon }}</span> {{ opt.label }}</button>
          </div>
        </div>

        <!-- Report type dropdown -->
        <div class="relative" ref="typeDropRef">
          <button
            @click="typeDropOpen = !typeDropOpen"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-sm text-gray-600 dark:text-gray-300 hover:border-indigo-400 transition min-w-36"
          >
            <span>{{ reportTypeLabels[reportType] }}</span>
            <svg class="w-4 h-4 ml-auto transition-transform" :class="typeDropOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div v-if="typeDropOpen" class="absolute z-10 mt-1 w-44 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl overflow-hidden">
            <button
              v-for="opt in reportTypeOptions" :key="opt.value"
              @click="reportType = opt.value; typeDropOpen = false; fetchReports()"
              class="w-full text-left px-4 py-2.5 text-sm transition flex items-center gap-2"
              :class="reportType === opt.value
                ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400'
                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
            >
              <span>{{ opt.icon }}</span> {{ opt.label }}
            </button>
          </div>
        </div>

        <!-- Status tabs -->
        <!-- Replace status tabs div with this dropdown -->
        <div class="relative" ref="statusDropRef">
          <button @click="statusDropOpen = !statusDropOpen"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-sm text-gray-600 dark:text-gray-300 hover:border-indigo-400 transition min-w-32">
            <span class="w-2 h-2 rounded-full" :class="{ 'bg-gray-400': activeTab === 'all', 'bg-yellow-400': activeTab === 'pending', 'bg-green-400': activeTab === 'reviewed', 'bg-gray-300': activeTab === 'dismissed' }"></span>
            <span>{{ tabs.find(t => t.value === activeTab)?.label }}</span>
            <svg class="w-4 h-4 ml-auto transition-transform" :class="statusDropOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div v-if="statusDropOpen" class="absolute z-10 mt-1 w-36 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl overflow-hidden right-0">
            <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value; statusDropOpen = false; fetchReports()"
              class="w-full text-left px-4 py-2.5 text-sm transition flex items-center gap-2"
              :class="activeTab === tab.value ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
            >{{ tab.label }}</button>
          </div>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center py-24">
        <div class="w-6 h-6 border-2 border-gray-300 border-t-red-500 rounded-full animate-spin"></div>
      </div>

      <div v-else class="flex flex-col gap-4">

        <!-- Post Reports -->
        <template v-if="reportType !== 'comment'">
          <div
            v-for="(report, i) in reports"
            :key="`post-${report.id}`"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden animate-fade-in-up"
            :style="{ animationDelay: `${i * 0.04}s` }"
          >
            <div class="flex flex-col sm:flex-row gap-4 p-4 sm:p-5">
              <div @click="openPostModal(report)" class="w-20 h-20 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0 cursor-pointer hover:opacity-80 transition">
                <img v-if="report.post?.image" :src="getImageUrl(report.post.image)" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-2xl">🎨</div>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                      <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-orange-50 dark:bg-orange-900/30 text-orange-500">📄 Post</span>
                      <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="topicColor(report.topic)">{{ topicLabel(report.topic) }}</span>
                      <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="statusColor(report.status || 'pending')">{{ report.status || 'pending' }}</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
                      <span class="font-medium text-indigo-500 cursor-pointer hover:text-indigo-700 transition" @click="selectedUserId = report.reporter?.id">{{ report.reporter?.name }}</span>
                      reported a post by
                      <span class="font-medium text-indigo-500 cursor-pointer hover:text-indigo-700 transition" @click="selectedUserId = report.post?.user?.id">{{ report.post?.user?.name }}</span>
                    </p>
                    <p v-if="report.details" class="text-xs text-gray-400 italic mb-1" style="white-space: pre-wrap; word-break: break-word;">"{{ report.details }}"</p>
                    <p class="text-xs text-gray-400">{{ formatDate(report.created_at) }}</p>
                  </div>

                  <div class="flex flex-wrap sm:flex-col gap-2 shrink-0">
                    <select v-model="report.status" @change="updatePostReportStatus(report)"
                      class="text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-1.5 focus:outline-none transition">
                      <option value="pending">Pending</option>
                      <option value="reviewed">Reviewed</option>
                      <option value="dismissed">Dismissed</option>
                    </select>
                    <button @click="openWarningModal(report)" class="text-xs text-amber-500 hover:text-amber-700 border border-amber-200 hover:border-amber-400 px-3 py-1.5 rounded-lg transition">⚠️ Warning</button>
                    <button @click="openDirectBan(report)" class="text-xs text-red-600 hover:text-red-800 border border-red-300 hover:border-red-500 px-3 py-1.5 rounded-lg transition">🚫 Ban User</button>
                    <button @click="deleteReportedPost(report)" class="text-xs text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-lg transition">Delete Post</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- Comment Reports -->
        <template v-if="reportType !== 'post'">
          <div
            v-for="(report, i) in commentReports"
            :key="`comment-${report.id}`"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden animate-fade-in-up"
            :style="{ animationDelay: `${i * 0.04}s` }"
          >
            <div class="p-5">
              <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-purple-50 dark:bg-purple-900/30 text-purple-500">💬 Comment</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="topicColor(report.topic)">{{ topicLabel(report.topic) }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="statusColor(report.status || 'pending')">{{ report.status || 'pending' }}</span>
                  </div>

                  <!-- Reported comment content -->
                  <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3 mb-2">
                    <div class="flex items-center gap-2 mb-1">
                      <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0 cursor-pointer" @click="selectedUserId = report.comment?.user?.id">
                        <img v-if="report.comment?.user?.avatar" :src="getImageUrl(report.comment.user.avatar)" class="w-full h-full object-cover" />
                        <span v-else>{{ report.comment?.user?.name?.charAt(0).toUpperCase() }}</span>
                      </div>
                      <span class="text-xs font-semibold text-indigo-500 cursor-pointer hover:text-indigo-700" @click="selectedUserId = report.comment?.user?.id">{{ report.comment?.user?.name }}</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300" style="white-space: pre-wrap; word-break: break-word;">
                      {{ report.comment?.content }}
                    </p>
                  </div>

                  <!-- Post it belongs to -->
                  <div v-if="report.comment?.post" @click="openCommentPostModal(report.comment.post)" class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition mb-2">
                    <div class="w-8 h-8 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0">
                      <img v-if="report.comment.post.image" :src="getImageUrl(report.comment.post.image)" class="w-full h-full object-cover" />
                      <div v-else class="w-full h-full flex items-center justify-center text-xs">🎨</div>
                    </div>
                    <p class="text-xs text-gray-400" style="white-space: pre-wrap; word-break: break-word;">
                      in: {{ report.comment.post.caption?.slice(0, 40) || report.comment.post.category }}
                    </p>
                  </div>

                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Reported by <span class="font-medium text-indigo-500 cursor-pointer hover:text-indigo-700" @click="selectedUserId = report.reporter?.id">{{ report.reporter?.name }}</span>
                  </p>
                  <p v-if="report.details" class="text-xs text-gray-400 italic mt-0.5">"{{ report.details }}"</p>
                  <p class="text-xs text-gray-400 mt-1">{{ formatDate(report.created_at) }}</p>
                </div>

                <div class="flex flex-wrap sm:flex-col gap-2 shrink-0">
                  <button @click="deleteReportedComment(report)" class="text-xs text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-lg transition">Delete Comment</button>
                  <button @click="openCommentWarning(report)" class="text-xs text-amber-500 hover:text-amber-700 border border-amber-200 hover:border-amber-400 px-3 py-1.5 rounded-lg transition">⚠️ Warning</button>
                  <button @click="openDirectBanComment(report)" class="text-xs text-red-600 hover:text-red-800 border border-red-300 hover:border-red-500 px-3 py-1.5 rounded-lg transition">🚫 Ban</button>
                  <button @click="dismissCommentReport(report)" class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 hover:border-gray-400 px-3 py-1.5 rounded-lg transition">Dismiss</button>
                </div>
              </div>
            </div>
          </div>
        </template>

        <p v-if="reports.length === 0 && commentReports.length === 0" class="text-center text-gray-400 text-sm py-12">No reports found</p>
      </div>

      <p v-if="msg" class="text-xs text-center" :class="msg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ msg }}</p>
    </div>

    <!-- Post View Modal -->
    <div v-if="activePost" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center px-4" @click.self="activePost = null">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex overflow-hidden">
        <div class="w-1/2 bg-black flex items-center justify-center shrink-0">
          <img v-if="activePost.image" :src="getImageUrl(activePost.image)" class="w-full h-full object-contain max-h-[90vh]" />
          <div v-else class="w-full p-8 flex">
            <div class="flex h-32 w-full rounded-xl overflow-hidden">
              <div v-for="(c, i) in (activePost.colors || [])" :key="i" class="flex-1" :style="{ backgroundColor: c }"></div>
            </div>
          </div>
        </div>
        <div class="flex-1 flex flex-col overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 cursor-pointer" @click="selectedUserId = activePost.user?.id; activePost = null">
              <div class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold overflow-hidden">
                <img v-if="activePost.user?.avatar" :src="getImageUrl(activePost.user.avatar)" class="w-full h-full object-cover" />
                <span v-else>{{ activePost.user?.name?.charAt(0).toUpperCase() }}</span>
              </div>
              <div>
                <p class="text-sm font-semibold text-indigo-500 hover:text-indigo-700 transition">{{ activePost.user?.name }}</p>
                <p class="text-xs text-gray-400">{{ activePost.category }}</p>
              </div>
            </div>
            <button @click="activePost = null" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
          </div>
          <div class="px-5 py-4 flex-1">
            <span class="inline-block bg-indigo-50 dark:bg-indigo-900/40 text-indigo-500 text-xs px-2.5 py-1 rounded-full mb-3">{{ activePost.category }}</span>
            <p v-if="activePost.caption" class="text-sm text-gray-600 dark:text-gray-300" style="white-space: pre-wrap; word-break: break-word;">
              {{ activePost.caption }}
            </p>
          </div>
          <div v-if="activePost.colors && activePost.colors.length" class="px-5 pb-4">
            <div class="flex gap-1 h-8 rounded-xl overflow-hidden">
              <div v-for="(c, i) in activePost.colors" :key="i" class="flex-1" :style="{ backgroundColor: c }"></div>
            </div>
          </div>
          <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex gap-3 text-xs text-gray-400">
            <span>❤️ {{ activePost.likes_count || 0 }}</span>
            <span>🔖 {{ activePost.saves_count || 0 }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- User Profile Modal -->
    <UserProfileModal v-if="selectedUserId" :userId="selectedUserId" @close="selectedUserId = null" />

    <!-- Warning Modal -->
    <div v-if="warningTarget" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center px-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[85vh] overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden p-6 flex flex-col gap-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-800 dark:text-white">⚠️ Send Warning</h2>
          <button @click="warningTarget = null" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3">
          <p class="text-xs font-medium text-amber-700 dark:text-amber-400">To: <span class="font-bold">{{ warningTarget.post?.user?.name }}</span></p>
          <p class="text-xs text-amber-600 mt-0.5">Report: {{ topicLabel(warningTarget.topic) }}</p>
        </div>

        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Auto warning message</p>
          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3 text-sm text-gray-600 dark:text-gray-300">{{ autoCaptions[warningTarget.topic] }}</div>
        </div>

        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Days before ban</p>
          <div class="flex gap-2">
            <button v-for="d in [1,3,5]" :key="d" @click="warningForm.expires_days = d"
              class="flex-1 py-2 rounded-xl border text-sm font-medium transition"
              :class="warningForm.expires_days === d ? 'bg-amber-500 text-white border-amber-500' : 'bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:border-amber-300'"
            >{{ d }} day{{ d > 1 ? 's' : '' }}</button>
          </div>
        </div>

        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Additional message (optional)</p>
          <textarea v-model="warningForm.admin_text" placeholder="Personal note..." rows="2"
            class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none resize-none transition" style="white-space: pre-wrap; word-break: break-word;"></textarea>
        </div>

        <p v-if="warningMsg" class="text-xs" :class="warningMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ warningMsg }}</p>

        <div class="flex gap-3">
          <button @click="warningTarget = null" class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 transition">Cancel</button>
          <button @click="sendWarning" :disabled="sendingWarning"
            class="flex-1 py-2.5 rounded-xl bg-amber-500 text-white text-sm font-medium disabled:opacity-40 transition">
            {{ sendingWarning ? 'Sending...' : '⚠️ Send Warning' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Direct Ban Modal -->
    <div v-if="directBanTarget" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center px-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 flex flex-col gap-4 max-h-[90vh] overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-800 dark:text-white">🚫 Direct Ban</h2>
          <button @click="directBanTarget = null" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-3">
          <p class="text-xs text-red-600 font-medium">Banning: <span class="font-bold">{{ directBanTarget.post?.user?.name || directBanTarget.comment?.user?.name }}</span></p>
          <p class="text-xs text-red-500 mt-0.5">Report: {{ topicLabel(directBanTarget.topic) }}</p>
        </div>

        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Auto Ban Message</p>
          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3 text-sm text-gray-600 dark:text-gray-300">{{ autoCaptions[directBanTarget.topic] }}</div>
        </div>

        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Ban Duration</p>
          <div class="grid grid-cols-3 gap-2">
            <button v-for="d in banDurations" :key="d.value" @click="directBanForm.duration = d.value"
              class="py-2 rounded-xl border text-xs font-medium transition text-center"
              :class="directBanForm.duration === d.value ? 'bg-red-500 text-white border-red-500' : 'bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:border-red-300'"
            >{{ d.label }}</button>
          </div>
        </div>

        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Admin Note (optional)</p>
          <textarea v-model="directBanForm.admin_reason" placeholder="Reason..." rows="2"
            class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none resize-none transition" style="white-space: pre-wrap; word-break: break-word;"></textarea>
        </div>

        <p v-if="directBanMsg" class="text-xs" :class="directBanMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ directBanMsg }}</p>

        <div class="flex gap-3">
          <button @click="directBanTarget = null" class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 transition">Cancel</button>
          <button @click="executeDirectBan" :disabled="!directBanForm.duration || directBanning"
            class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium disabled:opacity-40 hover:bg-red-600 transition">
            {{ directBanning ? 'Banning...' : '🚫 Execute Ban' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import UserProfileModal from './UserProfileModal.vue'

const emit = defineEmits(['navigate'])

function getImageUrl(path) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

function navigateAdmin(component) {
  emit('navigate', component)
}

const reports = ref([])
const commentReports = ref([])
const loading = ref(true)
const activeTab = ref('all')
const reportType = ref('all')
const search = ref('')
const msg = ref('')
const activePost = ref(null)
const selectedUserId = ref(null)
const warningTarget = ref(null)
const warningForm = ref({ expires_days: 1, admin_text: '' })
const warningMsg = ref('')
const sendingWarning = ref(false)
const typeDropOpen = ref(false)
const typeDropRef = ref(null)
const sortBy         = ref('newest')
const sortDropOpen   = ref(false)
const sortDropRef    = ref(null)
const statusDropOpen = ref(false)
const statusDropRef  = ref(null)
const directBanTarget = ref(null)
const directBanForm   = ref({ duration: '1d', admin_reason: '' })
const directBanMsg    = ref('')
const directBanning   = ref(false)
const quickActionsOpen = ref(false)
const quickActionsRef = ref(null)

const sortOptions = [
  { value: 'newest',     icon: '🆕', label: 'Newest'      },
  { value: 'oldest',     icon: '📅', label: 'Oldest'      },
  { value: 'this_week',  icon: '📆', label: 'This Week'   },
  { value: 'last_week',  icon: '📆', label: 'Last Week'   },
  { value: 'last_month', icon: '📆', label: 'Last Month'  },
  { value: 'last_year',  icon: '📆', label: 'Last Year'   },
]

const sortLabels = {
  newest: '🆕 Newest', oldest: '📅 Oldest',
  this_week: '📆 This Week', last_week: '📆 Last Week',
  last_month: '📆 Last Month', last_year: '📆 Last Year',
}

const banDurations = [
  { value: '1d', label: '1 Day' }, { value: '3d', label: '3 Days' },
  { value: '1w', label: '1 Week' }, { value: '1m', label: '1 Month' },
  { value: '3m', label: '3 Months' }, { value: '1y', label: '1 Year' },
  { value: 'permanent', label: 'Permanent' },
]

// Close dropdown on outside click
import { onBeforeUnmount } from 'vue'

function handleClickOutside(e) {
  if (typeDropRef.value && !typeDropRef.value.contains(e.target))     typeDropOpen.value   = false
  if (sortDropRef.value && !sortDropRef.value.contains(e.target))     sortDropOpen.value   = false
  if (statusDropRef.value && !statusDropRef.value.contains(e.target)) statusDropOpen.value = false
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
  fetchReports()
})

onBeforeUnmount(() => document.removeEventListener('mousedown', handleClickOutside))

const tabs = [
  { label: 'All',       value: 'all'       },
  { label: 'Pending',   value: 'pending'   },
  { label: 'Reviewed',  value: 'reviewed'  },
  { label: 'Dismissed', value: 'dismissed' },
]

const reportTypeOptions = [
  { value: 'all',     icon: '📋', label: 'All Reports'    },
  { value: 'post',    icon: '📄', label: 'Post Reports'   },
  { value: 'comment', icon: '💬', label: 'Comment Reports' },
]

const reportTypeLabels = {
  all:     '📋 All Reports',
  post:    '📄 Post Reports',
  comment: '💬 Comment Reports',
}

const autoCaptions = {
  spam:          'Your post has been flagged for spam. Repeated violations will result in a ban.',
  inappropriate: 'Your post contains inappropriate content that violates our community guidelines.',
  harassment:    'Your post has been flagged for harassment. This behavior is not tolerated.',
  copyright:     'Your post may contain copyrighted content without proper attribution.',
  other:         'Your post has been flagged for violating our community guidelines.',
}

async function fetchReports() {
  loading.value = true
  try {
    const params = new URLSearchParams({ status: activeTab.value, sort: sortBy.value })
    if (search.value) params.set('search', search.value)

    if (reportType.value !== 'comment') {
      const { data } = await axios.get(`/api/admin/reports?${params}`)
      reports.value = data.data || data
    } else {
      reports.value = []
    }

    if (reportType.value !== 'post') {
      const { data } = await axios.get(`/api/admin/comment-reports?${params}`)
      commentReports.value = data.data || data
    } else {
      commentReports.value = []
    }
  } catch (e) { msg.value = 'Failed to load reports.' }
  finally { loading.value = false }
}

function openPostModal(report) {
  if (report.post) activePost.value = { ...report.post }
}

function openCommentPostModal(post) {
  activePost.value = { ...post }
}

function openWarningModal(report) {
  warningTarget.value = report
  warningForm.value = { expires_days: 1, admin_text: '' }
  warningMsg.value = ''
}

async function sendWarning() {
  sendingWarning.value = true
  warningMsg.value = ''
  try {
    await axios.post('/api/admin/warnings', {
      user_id:         warningTarget.value.post?.user?.id,
      post_id:         warningTarget.value.post?.id,
      report_category: warningTarget.value.topic,
      admin_text:      warningForm.value.admin_text,
      expires_days:    warningForm.value.expires_days,
    })
    warningMsg.value = '✓ Warning sent!'
    setTimeout(() => { warningTarget.value = null; warningMsg.value = '' }, 2000)
  } catch (e) {
    warningMsg.value = e?.response?.data?.message || 'Failed.'
  } finally {
    sendingWarning.value = false
  }
}

async function updatePostReportStatus(report) {
  try {
    await axios.patch(`/api/admin/reports/${report.id}/status`, { status: report.status })
    msg.value = '✓ Status updated.'
    setTimeout(() => msg.value = '', 3000)
  } catch (e) { msg.value = 'Failed.' }
}

async function deleteReportedPost(report) {
  try {
    await axios.delete(`/api/admin/posts/${report.post_id}`)
    reports.value = reports.value.filter(r => r.post_id !== report.post_id)
    msg.value = '✓ Post deleted.'
    setTimeout(() => msg.value = '', 3000)
  } catch (e) { msg.value = 'Failed.' }
}

async function deleteReportedComment(report) {
  try {
    await axios.delete(`/api/admin/comments/${report.comment_id}`)
    commentReports.value = commentReports.value.filter(r => r.comment_id !== report.comment_id)
    msg.value = '✓ Comment deleted.'
    setTimeout(() => msg.value = '', 3000)
  } catch (e) { msg.value = 'Failed.' }
}

async function dismissCommentReport(report) {
  commentReports.value = commentReports.value.filter(r => r.id !== report.id)
  msg.value = '✓ Dismissed.'
  setTimeout(() => msg.value = '', 2000)
}

function topicLabel(topic) {
  const map = { spam: '📢 Spam', inappropriate: '🚫 Inappropriate', harassment: '😡 Harassment', copyright: '©️ Copyright', other: '❓ Other' }
  return map[topic] || topic
}

function topicColor(topic) {
  const map = { spam: 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600', inappropriate: 'bg-red-50 dark:bg-red-900/30 text-red-600', harassment: 'bg-orange-50 dark:bg-orange-900/30 text-orange-600', copyright: 'bg-purple-50 dark:bg-purple-900/30 text-purple-600', other: 'bg-gray-50 dark:bg-gray-700 text-gray-500' }
  return map[topic] || ''
}

function statusColor(status) {
  return { pending: 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600', reviewed: 'bg-green-50 dark:bg-green-900/30 text-green-600', dismissed: 'bg-gray-50 dark:bg-gray-700 text-gray-500' }[status] || ''
}

function formatDate(iso) {
  if (!iso) return '-'
  return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function openDirectBan(report) {
  directBanTarget.value = report
  directBanForm.value   = { duration: '1d', admin_reason: '' }
  directBanMsg.value    = ''
}

function openDirectBanComment(report) {
  // Adapt for comment report
  directBanTarget.value = { ...report, post: { user: report.comment?.user } }
  directBanForm.value   = { duration: '1d', admin_reason: '' }
  directBanMsg.value    = ''
}

async function executeDirectBan() {
  directBanning.value = true
  directBanMsg.value  = ''
  const userId = directBanTarget.value.post?.user?.id || directBanTarget.value.comment?.user?.id
  try {
    await axios.post(`/api/admin/users/${userId}/direct-ban`, {
      report_category: directBanTarget.value.topic,
      duration:        directBanForm.value.duration,
      admin_reason:    directBanForm.value.admin_reason,
    })
    directBanMsg.value = '✓ User banned!'
    setTimeout(() => { directBanTarget.value = null }, 2000)
  } catch (e) {
    directBanMsg.value = e?.response?.data?.message || 'Failed.'
  } finally {
    directBanning.value = false
  }
}

function openCommentWarning(report) {
  // Reuse the existing warning modal but for comment author
  warningTarget.value = {
    ...report,
    post: { user: report.comment?.user, id: report.comment?.post_id }
  }
  warningForm.value = { expires_days: 1, admin_text: '' }
  warningMsg.value  = ''
}
</script>