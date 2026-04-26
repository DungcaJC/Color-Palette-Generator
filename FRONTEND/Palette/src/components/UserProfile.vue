<template>
  <!--UserProfile.vue-->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <div class="bg-[#0d1117] pt-10 pb-20 px-8 text-center">
      <div class="max-w-3xl mx-auto">
        <h1 class="text-white text-2xl font-semibold">Your Profile</h1>
        <p class="text-gray-400 text-sm mt-1">Manage your personal information</p>
      </div>
    </div>

    <div class="max-w-3xl mx-auto px-8 -mt-12 pb-16">
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden animate-fade-in-up">

        <!-- Avatar -->
        <div class="flex items-center gap-6 px-8 py-6 border-b border-gray-100 dark:border-gray-700">
          <div class="relative group cursor-pointer shrink-0" @click="triggerAvatarUpload">
            <div
              class="w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl font-bold select-none overflow-hidden bg-indigo-600">
              <img v-if="avatarUrl" :src="avatarUrl" class="w-full h-full object-cover" />
              <span v-else>{{ userInitial }}</span>
            </div>
            <div
              class="absolute inset-0 rounded-full bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
              </svg>
            </div>
            <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="onAvatarChange" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-base font-semibold text-gray-800 dark:text-white">{{ user?.name }}</p>
            <p class="text-sm text-gray-400">{{ user?.email }}</p>
            <div class="flex items-center gap-1.5 mt-1">
              <span class="w-2 h-2 rounded-full"
                :class="user?.role === 'superadmin' ? 'bg-red-500' : user?.role === 'admin' ? 'bg-blue-500' : 'bg-green-500'"></span>
              <span class="text-xs"
                :class="user?.role === 'superadmin' ? 'text-red-400' : user?.role === 'admin' ? 'text-blue-400' : 'text-green-400'">{{
                user?.role }}</span>
            </div>
            <p v-if="avatarMsg" class="text-xs mt-1"
              :class="avatarMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ avatarMsg }}</p>
            <button v-else @click="triggerAvatarUpload"
              class="text-xs text-indigo-500 hover:text-indigo-700 mt-1 transition">Change photo</button>
          </div>
        </div>

        <!-- Stats -->
        <div
          class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-700 border-b border-gray-100 dark:border-gray-700">
          <div class="px-6 py-4 text-center">
            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ stats.total }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Palettes</p>
          </div>
          <div class="px-6 py-4 text-center">
            <p class="text-2xl font-bold text-orange-400">{{ myPosts.length }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Posts</p>
          </div>
          <div class="px-6 py-4 text-center">
            <p class="text-2xl font-bold text-red-400">{{ totalLikes }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Total Likes</p>
          </div>
        </div>

        <!-- Tab bar -->
        <div class="flex border-b border-gray-100 dark:border-gray-700">
          <button @click="activeTab = 'profile'" class="flex-1 py-3 text-sm font-medium transition border-b-2"
            :class="activeTab === 'profile' ? 'border-indigo-500 text-indigo-500' : 'border-transparent text-gray-400 hover:text-gray-600'">
            👤 Profile
          </button>
          <button @click="activeTab = 'posts'" class="flex-1 py-3 text-sm font-medium transition border-b-2"
            :class="activeTab === 'posts' ? 'border-indigo-500 text-indigo-500' : 'border-transparent text-gray-400 hover:text-gray-600'">
            🖼 My Posts
          </button>
          <button @click="activeTab = 'warnings'" class="flex-1 py-3 text-sm font-medium transition border-b-2"
            :class="activeTab === 'warnings' ? 'border-indigo-500 text-indigo-500' : 'border-transparent text-gray-400 hover:text-gray-600'">
            ⚠️ Warnings
          </button>
        </div>

        <!-- ─── Profile Tab ─── -->
        <div v-if="activeTab === 'profile'" class="animate-fade-in-up">

          <!-- Bio -->
          <div class="px-8 pt-6 pb-2">
            <label class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-2 block">Bio</label>
            <textarea v-model="bio" maxlength="500" rows="3" placeholder="Tell the community about yourself..."
              @input="autoResize($event)"
              class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition resize-none overflow-hidden"
              style="min-height: 80px; max-height: 200px;"></textarea>
            <div class="flex items-center justify-between mt-2">
              <p v-if="bioMsg" class="text-xs" :class="bioMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{
                bioMsg }}</p>
              <span class="text-xs text-gray-400 ml-auto">{{ bio.length }}/500</span>
            </div>
            <button @click="saveBio"
              class="mt-2 flex items-center gap-1.5 px-4 py-2 rounded-xl text-white text-xs font-semibold transition hover:opacity-90"
              style="background: linear-gradient(to right, #4f46e5, #f97316)">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
              Save Bio
            </button>
          </div>

          <!-- Form Fields -->
          <div class="px-8 py-6 flex flex-col gap-5">
            <div>
              <label class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-1.5 block">Display
                Name</label>
              <div class="flex gap-3">
                <input v-model="newName" type="text" :placeholder="user?.name || 'Your name'"
                  class="flex-1 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition" />
                <button @click="saveName" :disabled="!newName.trim() || newName === user?.name"
                  class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium disabled:opacity-40 hover:bg-indigo-500 transition">Save</button>
              </div>
              <p v-if="nameMsg" class="text-xs mt-1.5"
                :class="nameMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ nameMsg }}</p>
            </div>

            <div>
              <label class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-1.5 block">Email</label>
              <input :value="user?.email" type="email" disabled
                class="w-full border border-gray-100 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed" />
            </div>

            <div>
              <label class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-1.5 block">Member
                Since</label>
              <input :value="memberSince" disabled
                class="w-full border border-gray-100 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed" />
            </div>
          </div>

          <!-- Change Password -->
          <div class="px-8 py-6 border-t border-gray-100 dark:border-gray-700 flex flex-col gap-4">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Change Password</p>
            <div>
              <label class="text-xs text-gray-400 mb-1 block">Current Password</label>
              <input v-model="currentPassword" type="password" placeholder="••••••••"
                class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition" />
            </div>
            <div>
              <label class="text-xs text-gray-400 mb-1 block">New Password</label>
              <input v-model="newPassword" type="password" placeholder="••••••••"
                class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition" />
            </div>
            <div>
              <label class="text-xs text-gray-400 mb-1 block">Confirm New Password</label>
              <input v-model="confirmPassword" type="password" placeholder="••••••••"
                class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition" />
            </div>
            <p v-if="passwordError" class="text-xs text-red-500">{{ passwordError }}</p>
            <p v-if="passwordMsg" class="text-xs text-green-500">{{ passwordMsg }}</p>
            <button @click="changePassword" :disabled="!currentPassword || !newPassword || !confirmPassword"
              class="w-full py-3 rounded-xl bg-gray-800 dark:bg-gray-600 text-white text-sm font-medium disabled:opacity-40 hover:bg-gray-700 transition">
              Update Password
            </button>
          </div>
        </div>

        <!-- ─── My Posts Tab ─── -->
        <div v-if="activeTab === 'posts'" class="p-6 animate-fade-in-up">

          <!-- Summary stats -->
          <div class="grid grid-cols-3 gap-3 mb-5">
            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 text-center">
              <p class="text-xl font-bold text-gray-800 dark:text-white">{{ myPosts.length }}</p>
              <p class="text-xs text-gray-400 mt-0.5">Posts</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 text-center">
              <p class="text-xl font-bold text-red-400">{{ totalLikes }}</p>
              <p class="text-xs text-gray-400 mt-0.5">Total Likes</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 text-center">
              <p class="text-xl font-bold text-indigo-400">{{ totalSaves }}</p>
              <p class="text-xs text-gray-400 mt-0.5">Total Saves</p>
            </div>
          </div>

          <div v-if="myPosts.length === 0" class="text-center py-8 text-gray-400 text-sm">
            You haven't posted anything yet.
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div v-for="(post, i) in myPosts" :key="post.id" @click="openMyPost(post)"
              class="rounded-xl overflow-hidden cursor-pointer hover:opacity-80 transition bg-gray-100 dark:bg-gray-700 flex flex-col">
              <!-- Image or palette swatch -->
              <div class="aspect-square relative bg-gray-200 dark:bg-gray-600">
                <img v-if="post.image" :src="`http://localhost:8000/storage/${post.image}`"
                  class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex flex-col">
                  <div class="flex flex-1">
                    <div v-for="(c, ci) in (post.colors || []).slice(0, 5)" :key="ci" class="flex-1"
                      :style="{ backgroundColor: c }"></div>
                  </div>
                  <div class="flex items-center justify-center py-2 bg-white/10">
                    <span class="text-xs text-white">🎨 Palette</span>
                  </div>
                </div>
              </div>

              <!-- Stats strip -->
              <div class="px-2 py-1.5 flex items-center justify-between bg-white dark:bg-gray-800">
                <span class="text-xs text-gray-400 truncate max-w-16">{{ post.category }}</span>
                <div class="flex items-center gap-2 text-xs text-gray-400 shrink-0">
                  <span>❤️ {{ post.likes_count }}</span>
                  <span>🔖 {{ post.saves_count }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- ─── Warnings Tab ─── -->
        <div v-if="activeTab === 'warnings'" class="p-6 animate-fade-in-up">
          <div v-if="user?.strikes" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-3 mb-4">
            <p class="text-sm font-medium text-red-700 dark:text-red-400">⚠️ You have {{ user.strikes }} strike{{ user.strikes > 1 ? 's' : '' }}</p>
            <p class="text-xs text-red-500 mt-0.5">At 3 strikes = 1 day ban · 5 = 1 week · 10 = 1 month · 15 = 1 year</p>
          </div>

          <p v-if="warningsLoading" class="text-center py-8 text-gray-400">Loading...</p>
          <p v-else-if="myWarnings.length === 0" class="text-center py-8 text-gray-400 text-sm">No warnings. You're all
            good! ✅</p>
          <div v-else class="flex flex-col gap-4">
            <div v-for="warning in myWarnings" :key="warning.id"
              class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4">
              <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <span class="text-lg">⚠️</span>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Warning</span>
                    <span class="text-xs px-2 py-0.5 rounded-full" :class="{
                      'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600': warning.status === 'active',
                      'bg-gray-50 dark:bg-gray-700 text-gray-500': warning.status === 'expired',
                    }">{{ warning.status }}</span>
                  </div>
                  <p class="text-xs text-gray-500 mb-2">{{ formatDate(warning.created_at) }}</p>
                  <div
                    class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 mb-3">
                    <p class="text-xs font-medium text-amber-700 dark:text-amber-400">Category: {{
                      warning.report_category }}</p>
                    <p class="text-xs text-amber-600 dark:text-amber-300 mt-1">{{ warning.auto_caption ||
                      warning.admin_text }}</p>
                  </div>
                  <p v-if="warning.expires_at" class="text-xs text-gray-400 mb-3">Expires: {{
                    formatDate(warning.expires_at) }}</p>
                </div>
                <div class="shrink-0">
                  <button v-if="!warning.appeal" @click="openAppealModal(warning)"
                    class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-500 transition">
                    📤 Submit Appeal
                  </button>
                  <div v-else class="text-right">
                    <span class="text-xs px-2 py-1 rounded-full font-medium" :class="{
                      'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600': warning.appeal.status === 'pending',
                      'bg-green-50 dark:bg-green-900/30 text-green-600': warning.appeal.status === 'accepted',
                      'bg-red-50 dark:bg-red-900/30 text-red-600': warning.appeal.status === 'rejected',
                    }">{{ warning.appeal.status }}</span>
                    <p v-if="warning.appeal.admin_response" class="text-xs text-gray-500 mt-1">Admin response: {{
                      warning.appeal.admin_response }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ─── My Post View Modal (full Community-style) ─── -->
    <div v-if="activeMyPost" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center px-4"
      @click.self="activeMyPost = null">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex overflow-hidden">

        <!-- Left: image or palette -->
        <div class="w-1/2 bg-black flex items-center justify-center shrink-0">
          <img v-if="activeMyPost.image" :src="`http://localhost:8000/storage/${activeMyPost.image}`"
            class="w-full h-full object-contain max-h-[90vh]" />
          <div v-else class="w-full h-full flex flex-col p-8 gap-2">
            <div class="flex flex-1 rounded-xl overflow-hidden">
              <div v-for="(c, ci) in (activeMyPost.colors || [])" :key="ci" class="flex-1"
                :style="{ backgroundColor: c }"></div>
            </div>
          </div>
        </div>

        <!-- Right: details -->
        <div class="flex-1 flex flex-col overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">

          <!-- Header -->
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div
                class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold overflow-hidden shrink-0">
                <img v-if="user?.avatar" :src="`http://localhost:8000/storage/${user.avatar}`"
                  class="w-full h-full object-cover" />
                <span v-else>{{ userInitial }}</span>
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ user?.name }}</p>
                <p class="text-xs text-gray-400">{{ formatDate(activeMyPost.created_at) }}</p>
              </div>
            </div>
            <button @click="activeMyPost = null" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
          </div>

          <!-- Category + caption -->
          <div class="px-5 py-4">
            <span
              class="inline-block bg-indigo-50 dark:bg-indigo-900/40 text-indigo-500 text-xs px-2.5 py-1 rounded-full mb-3">{{
                activeMyPost.category }}</span>
            <p v-if="activeMyPost.caption" class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{
              activeMyPost.caption }}</p>
          </div>

          <!-- Palette colors -->
          <div v-if="activeMyPost.colors && activeMyPost.colors.length" class="px-5 pb-4">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Palette</p>
            <div class="flex gap-2 flex-wrap">
              <div v-for="(color, i) in activeMyPost.colors" :key="i"
                class="w-10 h-10 rounded-lg cursor-pointer hover:scale-110 transition-transform"
                :style="{ backgroundColor: color }" :title="color" @click="copyHex(color)"></div>
            </div>
            <p v-if="copiedHex" class="text-xs text-green-500 mt-1">✓ Copied {{ copiedHex }}</p>
          </div>

          <!-- Comments section -->
          <div
            class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex-1 flex flex-col overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-3">Comments ({{ comments.length }})</p>

            <div class="flex flex-col gap-3 mb-3">
              <div v-for="comment in comments" :key="comment.id" class="flex flex-col gap-1">
                <!-- Main comment -->
                <div class="flex gap-2 group">
                  <div
                    class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0">
                    <img v-if="comment.user?.avatar" :src="`http://localhost:8000/storage/${comment.user.avatar}`"
                      class="w-full h-full object-cover" />
                    <span v-else>{{ comment.user?.name?.charAt(0).toUpperCase() }}</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl px-3 py-2">
                      <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ comment.user?.name }}</p>
                      <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5">{{ comment.content }}</p>
                    </div>
                    <div class="flex items-center gap-3 mt-1 px-1">
                      <button @click="toggleCommentLike(comment)" class="text-xs transition"
                        :class="comment.liked_by_user ? 'text-red-500' : 'text-gray-400 hover:text-red-400'">
                        ❤️ {{ comment.likes_count }}
                      </button>
                      <button @click="replyTarget = comment; replyText = ''"
                        class="text-xs text-gray-400 hover:text-indigo-500 transition">Reply</button>
                      <button @click="openCommentReport(comment)"
                        class="text-xs text-gray-400 hover:text-gray-600 transition">Report</button>
                      <button v-if="comment.user_id === user?.id || isAdmin()" @click="deleteComment(comment.id)"
                        class="text-xs text-red-400 hover:text-red-600 transition opacity-0 group-hover:opacity-100">Delete</button>
                      <span class="text-xs text-gray-400 ml-auto">{{ formatDate(comment.created_at) }}</span>
                    </div>

                    <!-- Replies -->
                    <div v-if="comment.replies && comment.replies.length" class="mt-2 ml-4 flex flex-col gap-2">
                      <div v-for="reply in comment.replies" :key="reply.id" class="flex gap-2 group">
                        <div
                          class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0">
                          <img v-if="reply.user?.avatar" :src="`http://localhost:8000/storage/${reply.user.avatar}`"
                            class="w-full h-full object-cover" />
                          <span v-else>{{ reply.user?.name?.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl px-3 py-2">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ reply.user?.name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5">{{ reply.content }}</p>
                          </div>
                          <div class="flex items-center gap-3 mt-1 px-1">
                            <button @click="toggleCommentLike(reply)" class="text-xs transition"
                              :class="reply.liked_by_user ? 'text-red-500' : 'text-gray-400 hover:text-red-400'">❤️ {{
                              reply.likes_count }}</button>
                            <button v-if="reply.user_id === user?.id || isAdmin()" @click="deleteComment(reply.id)"
                              class="text-xs text-red-400 hover:text-red-600 transition opacity-0 group-hover:opacity-100">Delete</button>
                            <span class="text-xs text-gray-400 ml-auto">{{ formatDate(reply.created_at) }}</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Reply input -->
                    <div v-if="replyTarget?.id === comment.id" class="mt-2 ml-4 flex gap-2">
                      <input v-model="replyText" type="text" :placeholder="`Reply to ${comment.user?.name}...`"
                        class="flex-1 text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2 focus:outline-none focus:border-indigo-400 transition"
                        @keyup.enter="submitReply(comment.id)" />
                      <button @click="submitReply(comment.id)"
                        class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-xs transition hover:bg-indigo-500">Reply</button>
                      <button @click="replyTarget = null" class="text-xs text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <div v-if="reportCommentTarget"
                      class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center px-4">
                      <div
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm p-6 flex flex-col gap-4">
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white">Report Comment</h2>
                        <div class="flex flex-col gap-2">
                          <label v-for="topic in reportTopics" :key="topic.value"
                            class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                            :class="commentReport.topic === topic.value ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-400'">
                            <input type="radio" v-model="commentReport.topic" :value="topic.value" class="hidden" />
                            <span>{{ topic.icon }}</span>
                            <p class="text-sm text-gray-700 dark:text-gray-200">{{ topic.label }}</p>
                          </label>
                        </div>
                        <textarea v-model="commentReport.details" placeholder="Details (optional)" rows="2"
                          class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none resize-none"></textarea>
                        <p v-if="commentReportMsg" class="text-xs"
                          :class="commentReportMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{
                          commentReportMsg }}</p>
                        <div class="flex gap-3">
                          <button @click="reportCommentTarget = null"
                            class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 transition">Cancel</button>
                          <button @click="submitCommentReport" :disabled="!commentReport.topic || commentReporting"
                            class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium disabled:opacity-40 transition">Report</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <p v-if="!comments.length" class="text-xs text-gray-400 text-center py-3">No comments yet. Be the first!
              </p>
            </div>

            <!-- New comment input -->
            <div class="flex gap-2 mt-auto pt-2 border-t border-gray-100 dark:border-gray-700">
              <div
                class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0">
                <img v-if="user?.avatar" :src="`http://localhost:8000/storage/${user.avatar}`"
                  class="w-full h-full object-cover" />
                <span v-else>{{ userInitial }}</span>
              </div>
              <div class="flex-1 flex gap-2">
                <input v-model="newComment" type="text" placeholder="Write a comment..."
                  class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2 focus:outline-none focus:border-indigo-400 transition"
                  @keyup.enter="submitComment" />
                <button @click="submitComment" :disabled="!newComment.trim()"
                  class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-sm disabled:opacity-40 transition hover:bg-indigo-500">Post</button>
              </div>
            </div>
          </div>

          <!-- Actions bar -->
          <div
            class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-4">
              <!-- Like -->
              <button @click="toggleLike(activeMyPost)" class="flex items-center gap-1.5 text-sm transition"
                :class="activeMyPost.liked_by_user ? 'text-red-500' : 'text-gray-400 hover:text-red-400'">
                <span class="text-lg">{{ activeMyPost.liked_by_user ? '❤️' : '🤍' }}</span>
                <span>{{ activeMyPost.likes_count }}</span>
              </button>

              <!-- Save -->
              <button @click="toggleSavePost(activeMyPost)" class="flex items-center gap-1.5 text-sm transition"
                :class="activeMyPost.saved_by_user ? 'text-indigo-500' : 'text-gray-400 hover:text-indigo-400'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                <span>{{ activeMyPost.saved_by_user ? 'Saved' : 'Save' }}</span>
              </button>
            </div>

            <div class="flex items-center gap-3">
              <!-- Edit -->
              <button @click="openEditModal(activeMyPost)"
                class="text-xs text-indigo-400 hover:text-indigo-600 border border-indigo-200 px-3 py-1 rounded-full transition">
                Edit
              </button>
              <!-- Delete -->
              <button @click="confirmDeletePost(activeMyPost)"
                class="text-xs text-red-400 hover:text-red-600 border border-red-200 px-3 py-1 rounded-full transition">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── Delete Confirm Modal ─── -->
    <div v-if="deleteTarget" class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center px-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-sm">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-2">Delete post?</h2>
        <p class="text-sm text-gray-400 mb-6">This will permanently delete the post and image.</p>
        <div class="flex gap-3">
          <button @click="deleteTarget = null"
            class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 transition">Cancel</button>
          <button @click="deletePost"
            class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium transition">Delete</button>
        </div>
      </div>
    </div>

    <!-- ─── Edit Post Modal ─── -->
    <div v-if="editTarget" class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center px-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 flex flex-col gap-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-800 dark:text-white">Edit Post</h2>
          <button @click="editTarget = null" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <textarea v-model="editForm.caption" placeholder="Caption..." rows="3" @input="autoResize($event)"
          class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 text-sm focus:outline-none resize-none transition"
          style="min-height: 80px; max-height: 200px;"></textarea>

        <select v-if="editTarget.post_type !== 'palette'" v-model="editForm.category"
          class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none transition">
          <option v-for="cat in postCategories" :key="cat" :value="cat">{{ cat }}</option>
        </select>

        <p v-if="editMsg" class="text-xs" :class="editMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ editMsg
          }}</p>

        <div class="flex gap-3">
          <button @click="editTarget = null"
            class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 transition">Cancel</button>
          <button @click="saveEdit" :disabled="savingEdit"
            class="flex-1 py-2.5 rounded-xl text-white text-sm font-medium disabled:opacity-40 transition"
            style="background: linear-gradient(to right, #4f46e5, #f97316)">
            {{ savingEdit ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ─── Appeal Modal ─── -->
    <div v-if="appealModal" class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center px-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 flex flex-col gap-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-800 dark:text-white">Submit Appeal</h2>
          <button @click="appealModal = null" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
          <p class="text-xs font-medium text-amber-700 dark:text-amber-400">Warning Category: {{
            appealModal.report_category }}</p>
          <p class="text-xs text-amber-600 dark:text-amber-300 mt-1">{{ appealModal.auto_caption ||
            appealModal.admin_text }}</p>
        </div>

        <textarea v-model="appealText" placeholder="Write your apology and explanation..." rows="4"
          class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 text-sm focus:outline-none resize-none transition"></textarea>

        <div>
          <label class="text-xs text-gray-400 mb-1 block">Proof Images (optional, max 5)</label>
          <input type="file" multiple accept="image/*" @change="onAppealImagesChange" class="text-sm text-gray-500" />
        </div>

        <p v-if="appealMsg" class="text-xs" :class="appealMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{
          appealMsg }}</p>

        <div class="flex gap-3">
          <button @click="appealModal = null"
            class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 transition">Cancel</button>
          <button @click="submitAppeal" :disabled="!appealText.trim() || appealSubmitting"
            class="flex-1 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium disabled:opacity-40 transition">
            {{ appealSubmitting ? 'Submitting...' : 'Submit Appeal' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useAuth } from '../composables/useAuth'
import { usePaletteStore } from '../composables/usePaletteStore'

const { user, isAdmin } = useAuth()
const { getAll } = usePaletteStore()

// ─── Avatar / profile fields ───────────────────────────
const avatarInput = ref(null)
const avatarUrl = ref(user.value?.avatar ? `http://localhost:8000/storage/${user.value.avatar}` : null)
const avatarMsg = ref('')
const newName = ref(user.value?.name || '')
const nameMsg = ref('')
const bio = ref(user.value?.bio || '')
const bioMsg = ref('')
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const passwordError = ref('')
const passwordMsg = ref('')

// ─── Posts ─────────────────────────────────────────────
const stats = ref({ total: 0, created: 0, image: 0, keyword: 0 })
const activeTab = ref('profile')
const myPosts = ref([])

// ─── Active post modal ─────────────────────────────────
const activeMyPost = ref(null)
const copiedHex = ref('')

// ─── Comments ──────────────────────────────────────────
const comments = ref([])
const newComment = ref('')
const replyTarget = ref(null)
const replyText = ref('')

const reportTopics = [
  { value: 'spam', icon: '📢', label: 'Spam', desc: 'Unwanted commercial content' },
  { value: 'inappropriate', icon: '🚫', label: 'Inappropriate', desc: 'Offensive or adult content' },
  { value: 'harassment', icon: '😡', label: 'Harassment', desc: 'Bullying or targeted attacks' },
  { value: 'copyright', icon: '©️', label: 'Copyright', desc: 'Stolen content' },
  { value: 'other', icon: '❓', label: 'Other', desc: 'Something else' },
]

// Comment report
const reportCommentTarget = ref(null)
const commentReport = ref({ topic: '', details: '' })
const commentReporting = ref(false)
const commentReportMsg = ref('')

function openCommentReport(comment) {
  reportCommentTarget.value = comment
  commentReport.value = { topic: '', details: '' }
  commentReportMsg.value = ''
}

async function submitCommentReport() {
  commentReporting.value = true
  try {
    await axios.post(`/api/comments/${reportCommentTarget.value.id}/report`, commentReport.value)
    commentReportMsg.value = '✓ Reported!'
    setTimeout(() => { reportCommentTarget.value = null }, 2000)
  } catch (e) { commentReportMsg.value = 'Failed.' }
  finally { commentReporting.value = false }
}

// ─── Delete / Edit ─────────────────────────────────────
const deleteTarget = ref(null)
const editTarget = ref(null)
const editForm = ref({ caption: '', category: '' })
const editMsg = ref('')
const savingEdit = ref(false)

// ─── Warnings / Appeals ───────────────────────────────
const myWarnings = ref([])
const warningsLoading = ref(false)
const appealModal = ref(null)
const appealText = ref('')
const appealImages = ref([])
const appealMsg = ref('')
const appealSubmitting = ref(false)

const postCategories = ['Abstract', 'Nature', 'Portrait', 'Architecture', 'Food', 'Fashion', 'Digital Art', 'Photography', 'Other']

// ─── Computed ──────────────────────────────────────────
const userInitial = computed(() => user.value?.name?.charAt(0).toUpperCase() || '?')
const memberSince = computed(() => {
  if (!user.value?.created_at) return 'N/A'
  return new Date(user.value.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
})
const totalLikes = computed(() => myPosts.value.reduce((sum, p) => sum + (p.likes_count || 0), 0))
const totalSaves = computed(() => myPosts.value.reduce((sum, p) => sum + (p.saves_count || 0), 0))

// ─── Lifecycle ─────────────────────────────────────────
onMounted(async () => {
  const palettes = await getAll()
  stats.value = {
    total: palettes.length,
    created: palettes.filter(p => p.source === 'created').length,
    image: palettes.filter(p => p.source === 'image').length,
    keyword: palettes.filter(p => p.source === 'keyword').length,
  }
  try {
    const { data } = await axios.get('/api/my-posts')
    myPosts.value = data
  } catch (e) { console.error(e) }

  // Fetch warnings for the Warnings tab
  await fetchWarnings()
})

// ─── Open post — load comments ─────────────────────────
async function openMyPost(post) {
  activeMyPost.value = { ...post }
  comments.value = []
  replyTarget.value = null
  newComment.value = ''
  try {
    const { data } = await axios.get(`/api/posts/${post.id}/comments`)
    comments.value = data
  } catch (e) { console.error(e) }
}

// ─── Comments ──────────────────────────────────────────
async function submitComment() {
  if (!newComment.value.trim() || !activeMyPost.value) return
  try {
    const { data } = await axios.post(`/api/posts/${activeMyPost.value.id}/comments`, { content: newComment.value.trim() })
    comments.value.unshift({ ...data, likes_count: 0, liked_by_user: 0, replies: [] })
    newComment.value = ''
  } catch (e) { console.error(e) }
}

async function submitReply(parentId) {
  if (!replyText.value.trim() || !activeMyPost.value) return
  try {
    const { data } = await axios.post(`/api/posts/${activeMyPost.value.id}/comments`, {
      content: replyText.value.trim(),
      parent_id: parentId,
    })
    const parent = comments.value.find(c => c.id === parentId)
    if (parent) parent.replies = [...(parent.replies || []), { ...data, likes_count: 0, liked_by_user: 0 }]
    replyText.value = ''
    replyTarget.value = null
  } catch (e) { console.error(e) }
}

async function toggleCommentLike(comment) {
  try {
    const { data } = await axios.post(`/api/comments/${comment.id}/like`)
    comment.liked_by_user = data.liked ? 1 : 0
    comment.likes_count = data.likes_count
  } catch (e) { console.error(e) }
}

async function deleteComment(commentId) {
  try {
    await axios.delete(`/api/comments/${commentId}`)
    comments.value = comments.value.filter(c => c.id !== commentId)
    comments.value.forEach(c => {
      if (c.replies) c.replies = c.replies.filter(r => r.id !== commentId)
    })
  } catch (e) { console.error(e) }
}

// ─── Like / Save ───────────────────────────────────────
async function toggleLike(post) {
  try {
    const { data } = await axios.post(`/api/posts/${post.id}/like`)
    post.liked_by_user = data.liked ? 1 : 0
    post.likes_count = data.likes_count
    // sync the grid card
    const card = myPosts.value.find(p => p.id === post.id)
    if (card) { card.liked_by_user = post.liked_by_user; card.likes_count = post.likes_count }
  } catch (e) { console.error(e) }
}

async function toggleSavePost(post) {
  try {
    const { data } = await axios.post(`/api/posts/${post.id}/save`)
    post.saved_by_user = data.saved ? 1 : 0
    const card = myPosts.value.find(p => p.id === post.id)
    if (card) card.saved_by_user = post.saved_by_user
  } catch (e) { console.error(e) }
}

async function copyHex(hex) {
  await navigator.clipboard.writeText(hex)
  copiedHex.value = hex
  setTimeout(() => copiedHex.value = '', 2000)
}

// ─── Delete ────────────────────────────────────────────
function confirmDeletePost(post) {
  deleteTarget.value = post
  activeMyPost.value = null
}

async function deletePost() {
  try {
    await axios.delete(`/api/posts/${deleteTarget.value.id}`)
    myPosts.value = myPosts.value.filter(p => p.id !== deleteTarget.value.id)
    deleteTarget.value = null
  } catch (e) { console.error(e) }
}

// ─── Edit ──────────────────────────────────────────────
function openEditModal(post) {
  editTarget.value = post
  editForm.value = { caption: post.caption || '', category: post.category || 'Other' }
  editMsg.value = ''
  activeMyPost.value = null
}

async function saveEdit() {
  savingEdit.value = true
  editMsg.value = ''
  try {
    const { data } = await axios.put(`/api/posts/${editTarget.value.id}`, {
      caption: editForm.value.caption,
      category: editForm.value.category,
      colors: editTarget.value.colors,
    })
    const idx = myPosts.value.findIndex(p => p.id === data.id)
    if (idx !== -1) myPosts.value[idx] = { ...myPosts.value[idx], ...data }
    editMsg.value = '✓ Post updated!'
    setTimeout(() => { editTarget.value = null; editMsg.value = '' }, 1500)
  } catch (e) {
    editMsg.value = e?.response?.data?.message || 'Failed to update.'
  } finally {
    savingEdit.value = false
  }
}

// ─── Warnings / Appeals ────────────────────────────────
async function fetchWarnings() {
  warningsLoading.value = true
  try {
    const { data } = await axios.get('/api/my-warnings')
    myWarnings.value = Array.isArray(data) ? data : []
    console.log('Warnings loaded:', myWarnings.value.length)
  } catch (e) {
    console.error('Failed to fetch warnings:', e?.response?.data || e)
    myWarnings.value = []
  } finally {
    warningsLoading.value = false
  }
}


function openAppealModal(warning) {
  appealModal.value = warning
  appealText.value = ''
  appealImages.value = []
  appealMsg.value = ''
}

function onAppealImagesChange(e) {
  const files = Array.from(e.target.files)
  if (files.length > 5) {
    alert('Maximum 5 images allowed')
    e.target.value = ''
    return
  }
  appealImages.value = files
}

async function submitAppeal() {
  if (!appealText.value.trim() || !appealModal.value) return
  appealSubmitting.value = true
  appealMsg.value = ''
  try {
    const formData = new FormData()
    formData.append('apology_text', appealText.value.trim())
    appealImages.value.forEach(file => formData.append('images[]', file))

    const { data } = await axios.post(`/api/warnings/${appealModal.value.id}/appeal`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    // Update the warning in the list with the new appeal
    const idx = myWarnings.value.findIndex(w => w.id === appealModal.value.id)
    if (idx !== -1) {
      myWarnings.value[idx] = { ...myWarnings.value[idx], appeal: data }
    }

    appealMsg.value = '✓ Appeal submitted!'
    setTimeout(() => { appealModal.value = null }, 2000)
  } catch (e) {
    appealMsg.value = e?.response?.data?.message || 'Failed to submit appeal.'
  } finally {
    appealSubmitting.value = false
  }
}

// ─── Avatar ────────────────────────────────────────────
function triggerAvatarUpload() { avatarInput.value.click() }

async function onAvatarChange(e) {
  const file = e.target.files[0]
  if (!file) return
  if (file.size > 2 * 1024 * 1024) { avatarMsg.value = 'Image must be under 2MB.'; return }
  const reader = new FileReader()
  reader.onload = ev => { avatarUrl.value = ev.target.result }
  reader.readAsDataURL(file)
  try {
    const formData = new FormData()
    formData.append('avatar', file)
    const { data } = await axios.post('/api/user/avatar', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    if (user.value) user.value.avatar = data.avatar
    avatarUrl.value = `http://localhost:8000/storage/${data.avatar}`
    avatarMsg.value = '✓ Photo updated!'
    setTimeout(() => avatarMsg.value = '', 3000)
  } catch (e) { avatarMsg.value = 'Failed.'; setTimeout(() => avatarMsg.value = '', 3000) }
}

// ─── Profile fields ────────────────────────────────────
async function saveName() {
  try {
    await axios.put('/api/user/name', { name: newName.value.trim() })
    if (user.value) user.value.name = newName.value.trim()
    nameMsg.value = '✓ Name updated!'
    setTimeout(() => nameMsg.value = '', 3000)
  } catch { nameMsg.value = 'Failed.' }
}

async function saveBio() {
  try {
    await axios.put('/api/user/bio', { bio: bio.value })
    if (user.value) user.value.bio = bio.value
    bioMsg.value = '✓ Bio updated!'
    setTimeout(() => bioMsg.value = '', 3000)
  } catch { bioMsg.value = 'Failed.' }
}

async function changePassword() {
  passwordError.value = ''
  passwordMsg.value = ''
  if (newPassword.value !== confirmPassword.value) { passwordError.value = 'Passwords do not match.'; return }
  if (newPassword.value.length < 8) { passwordError.value = 'Must be at least 8 characters.'; return }
  try {
    await axios.put('/api/user/password', { current_password: currentPassword.value, new_password: newPassword.value })
    passwordMsg.value = '✓ Password updated!'
    currentPassword.value = ''; newPassword.value = ''; confirmPassword.value = ''
    setTimeout(() => passwordMsg.value = '', 3000)
  } catch (e) { passwordError.value = e?.response?.data?.message || 'Incorrect current password.' }
}

// ─── Helpers ───────────────────────────────────────────
function autoResize(e) {
  e.target.style.height = 'auto'
  e.target.style.height = e.target.scrollHeight + 'px'
}

function formatDate(iso) {
  if (!iso) return ''
  const d = new Date(iso)
  const now = new Date()
  const diff = Math.floor((now - d) / 1000)
  if (diff < 60) return 'just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}
</script>