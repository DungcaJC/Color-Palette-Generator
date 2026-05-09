<template>
  <!-- UserProfileModal.vue -->
  <div class="fixed inset-0 z-[60] bg-black/70 flex items-center justify-center px-4" @click.self="$emit('close')">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden animate-fade-in-up">

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-24">
        <div class="w-8 h-8 border-2 border-gray-300 border-t-indigo-600 rounded-full animate-spin"></div>
      </div>

      <template v-else-if="profile">
        <!-- Header -->
        <div class="bg-[#0d1117] rounded-t-2xl relative overflow-hidden">
          <!-- Decorative background gradient -->
          <div class="absolute inset-0 opacity-30" style="background: radial-gradient(ellipse at top right, #6366f1, transparent 60%), radial-gradient(ellipse at bottom left, #f97316, transparent 60%)"></div>

          <!-- Close button -->
          <button @click="$emit('close')" class="absolute top-4 right-4 text-gray-400 hover:text-white text-lg z-10">✕</button>

          <!-- Main header content -->
          <div class="relative z-10 p-4 sm:p-6 flex flex-col sm:flex-row gap-4 sm:gap-5">

            <!-- Avatar -->
            <div class="w-20 h-20 rounded-full bg-indigo-600 flex items-center justify-center text-white text-3xl font-bold overflow-hidden shrink-0 ring-4 ring-white/10">
              <img v-if="profile.user.avatar" :src="getImageUrl(profile.user.avatar)" class="w-full h-full object-cover" />
              <span v-else>{{ profile.user.name?.charAt(0).toUpperCase() }}</span>
            </div>

            <!-- Left info — name, role, email, bio -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-white text-xl font-bold">{{ profile.user.name }}</h2>
                <span class="w-2 h-2 rounded-full shrink-0" :class="roleColor(profile.user.role)"></span>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full" :class="{
                  'bg-red-500/20 text-red-400': profile.user.role === 'superadmin',
                  'bg-blue-500/20 text-blue-400': profile.user.role === 'admin',
                  'bg-green-500/20 text-green-400': profile.user.role === 'user',
                }">{{ profile.user.role }}</span>
              </div>
              <p class="text-gray-400 text-sm mt-0.5">{{ profile.user.email }}</p>
              <p v-if="profile.user.bio" class="text-gray-300 text-sm mt-1.5 leading-relaxed line-clamp-2" style="white-space: pre-wrap; word-break: break-word;">{{ profile.user.bio }}</p>
              <p v-else class="text-gray-500 text-sm mt-1.5 italic">No bio yet.</p>
              <p class="text-gray-600 text-xs mt-1.5">Member since {{ formatDateLong(profile.user.created_at) }}</p>
            </div>

            <!-- Right side — follow stats + button -->
            <div class="flex flex-row sm:flex-col items-center sm:items-end justify-between sm:pr-6 w-full sm:w-auto">
              <!-- Follow button -->
              <button
                v-if="currentUserId && currentUserId !== profile.user.id"
                @click="toggleFollow"
                :disabled="followLoading"
                class="px-5 py-2 rounded-xl text-sm font-semibold transition mt-1"
                :class="isFollowing
                  ? 'bg-white/10 text-white hover:bg-red-500/20 hover:text-red-300 border border-white/20'
                  : 'bg-indigo-600 text-white hover:bg-indigo-500'"
              >
                {{ followLoading ? '...' : isFollowing ? 'Unfollow' : '+ Follow' }}
              </button>
              <div v-else class="mt-1 h-8"></div>

              <!-- Follower / Following counts -->
              <div class="flex items-center gap-5">
                <button @click="openFollowers" class="text-center hover:opacity-70 transition group">
                  <p class="text-white text-lg font-bold group-hover:text-indigo-300 transition">{{ profile.user.followers_count || 0 }}</p>
                  <p class="text-gray-400 text-xs">Followers</p>
                </button>
                <div class="w-px h-8 bg-white/10"></div>
                <button @click="openFollowing" class="text-center hover:opacity-70 transition group">
                  <p class="text-white text-lg font-bold group-hover:text-indigo-300 transition">{{ profile.user.following_count || 0 }}</p>
                  <p class="text-gray-400 text-xs">Following</p>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 border-b border-gray-100 dark:border-gray-700">
          <div class="text-center py-4 border-r border-gray-100 dark:border-gray-700">
            <p class="text-xl font-bold text-gray-800 dark:text-white">{{ profile.posts.length }}</p>
            <p class="text-xs text-gray-400">Posts</p>
          </div>
          <div class="text-center py-4 border-r border-gray-100 dark:border-gray-700">
            <p class="text-xl font-bold text-red-400">{{ totalLikes }}</p>
            <p class="text-xs text-gray-400">Likes</p>
          </div>
          <div class="text-center py-4 border-r border-gray-100 dark:border-gray-700">
            <p class="text-xl font-bold text-indigo-400">{{ profile.palettes.length }}</p>
            <p class="text-xs text-gray-400">Palettes</p>
          </div>
          <div class="text-center py-4">
            <p class="text-xl font-bold text-teal-400">{{ totalSaves }}</p>
            <p class="text-xs text-gray-400">Saves</p>
          </div>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-100 dark:border-gray-700">
          <button @click="tab = 'posts'" class="flex-1 py-3 text-sm font-medium transition border-b-2" :class="tab === 'posts' ? 'border-indigo-500 text-indigo-500' : 'border-transparent text-gray-400 hover:text-gray-600'">🖼 Posts</button>
          <button @click="tab = 'palettes'" class="flex-1 py-3 text-sm font-medium transition border-b-2" :class="tab === 'palettes' ? 'border-indigo-500 text-indigo-500' : 'border-transparent text-gray-400 hover:text-gray-600'">🎨 Palettes</button>
        </div>

        <!-- Posts tab -->
        <div v-if="tab === 'posts'" class="p-5 animate-fade-in-up">
          <div v-if="!profile.posts.length" class="text-center py-8 text-gray-400 text-sm">No posts yet</div>
          <div class="grid grid-cols-3 gap-3">
            <div
              v-for="post in profile.posts" :key="post.id"
              @click="openSubPost(post)"
              class="rounded-xl overflow-hidden cursor-pointer hover:opacity-80 transition bg-gray-100 dark:bg-gray-700"
              style="display: grid; grid-template-rows: 1fr auto;"
            >
              <div class="relative overflow-hidden" style="aspect-ratio: 1/1;">
                <img v-if="post.image" :src="getImageUrl(post.image)" class="absolute inset-0 w-full h-full object-cover" />
                <div v-else class="absolute inset-0 flex">
                  <div v-for="(c, ci) in (post.colors || []).slice(0, 5)" :key="ci" class="flex-1" :style="{ backgroundColor: c }"></div>
                </div>
              </div>
              <div class="px-2 py-1.5 flex items-center justify-between bg-white dark:bg-gray-800 shrink-0">
                <span class="text-xs text-gray-400 truncate" style="max-width:60px">{{ post.category }}</span>
                <div class="flex items-center gap-2 text-xs text-gray-400 shrink-0">
                  <span>❤️ {{ post.likes_count || 0 }}</span>
                  <span>🔖 {{ post.saves_count || 0 }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Palettes tab -->
        <div v-if="tab === 'palettes'" class="p-5 animate-fade-in-up">
          <div v-if="!profile.palettes.length" class="text-center py-8 text-gray-400 text-sm">No palettes yet</div>
          <div class="flex flex-col gap-3">
            <div v-for="palette in profile.palettes" :key="palette.id" class="rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700">
              <div class="flex h-12">
                <div v-for="(c, ci) in palette.colors" :key="ci" class="flex-1" :style="{ backgroundColor: c }"></div>
              </div>
              <div class="px-3 py-2 flex items-center justify-between bg-white dark:bg-gray-800">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ palette.name }}</p>
                <span class="text-xs text-gray-400">{{ palette.source }}</span>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>

  <!-- Followers Modal -->
  <div v-if="showFollowers" class="fixed inset-0 z-[80] bg-black/70 flex items-center justify-center px-4" @click.self="showFollowers = false">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm max-h-[70vh] flex flex-col overflow-hidden animate-fade-in-up">
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white">Followers</h2>
        <button @click="showFollowers = false" class="text-gray-400 hover:text-gray-600">✕</button>
      </div>
      <div class="flex-1 overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <div v-if="followListLoading" class="flex justify-center py-8"><div class="w-6 h-6 border-2 border-gray-300 border-t-indigo-600 rounded-full animate-spin"></div></div>
        <div v-else-if="!followersList.length" class="text-center py-8 text-gray-400 text-sm">No followers yet</div>
        <div v-else class="flex flex-col divide-y divide-gray-100 dark:divide-gray-700">
          <div v-for="person in followersList" :key="person.id" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold overflow-hidden shrink-0">
              <img v-if="person.avatar" :src="getImageUrl(person.avatar)" class="w-full h-full object-cover" />
              <span v-else>{{ person.name?.charAt(0).toUpperCase() }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate">{{ person.name }}</p>
              <p class="text-xs text-gray-400 truncate" style="white-space: pre-wrap; word-break: break-word;">{{ person.bio || 'No bio' }}</p>
            </div>
            <span class="w-2 h-2 rounded-full shrink-0" :class="person.role === 'superadmin' ? 'bg-red-500' : person.role === 'admin' ? 'bg-blue-500' : 'bg-green-500'"></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Following Modal -->
  <div v-if="showFollowing" class="fixed inset-0 z-[80] bg-black/70 flex items-center justify-center px-4" @click.self="showFollowing = false">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm max-h-[70vh] flex flex-col overflow-hidden animate-fade-in-up">
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white">Following</h2>
        <button @click="showFollowing = false" class="text-gray-400 hover:text-gray-600">✕</button>
      </div>
      <div class="flex-1 overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <div v-if="followListLoading" class="flex justify-center py-8"><div class="w-6 h-6 border-2 border-gray-300 border-t-indigo-600 rounded-full animate-spin"></div></div>
        <div v-else-if="!followingList.length" class="text-center py-8 text-gray-400 text-sm">Not following anyone yet</div>
        <div v-else class="flex flex-col divide-y divide-gray-100 dark:divide-gray-700">
          <div v-for="person in followingList" :key="person.id" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold overflow-hidden shrink-0">
              <img v-if="person.avatar" :src="getImageUrl(person.avatar)" class="w-full h-full object-cover" />
              <span v-else>{{ person.name?.charAt(0).toUpperCase() }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate">{{ person.name }}</p>
              <p class="text-xs text-gray-400 truncate" style="white-space: pre-wrap; word-break: break-word;">{{ person.bio || 'No bio' }}</p>
            </div>
            <span class="w-2 h-2 rounded-full shrink-0" :class="person.role === 'superadmin' ? 'bg-red-500' : person.role === 'admin' ? 'bg-blue-500' : 'bg-green-500'"></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ─── Sub-post view modal (full Community style) ─── -->
  <div v-if="activeSubPost" class="fixed inset-0 z-[70] bg-black/70 flex items-center justify-center px-4" @click.self="activeSubPost = null">
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl flex flex-col md:flex-row overflow-hidden" style="max-height:90vh">
      <!-- Left: image or palette -->
      <div class="w-full md:w-5/12 bg-black flex items-center justify-center shrink-0 max-h-56 sm:max-h-72 md:max-h-full">
        <img v-if="activeSubPost.image" :src="getImageUrl(activeSubPost.image)" class="w-full h-full object-contain" />
        <div v-else class="w-full h-full flex flex-col p-6 gap-3 items-center justify-center">
          <div class="flex w-full h-32 rounded-xl overflow-hidden">
            <div v-for="(c, ci) in (activeSubPost.colors || [])" :key="ci" class="flex-1" :style="{ backgroundColor: c }"></div>
          </div>
          <p class="text-gray-400 text-xs">🎨 Palette post</p>
        </div>
      </div>

      <!-- Right: details + comments -->
      <div class="flex-1 flex flex-col overflow-hidden">

        <!-- User header — fixed -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700 shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold overflow-hidden shrink-0">
              <img v-if="activeSubPost.user?.avatar" :src="getImageUrl(activeSubPost.user.avatar)" class="w-full h-full object-cover" />
              <span v-else>{{ activeSubPost.user?.name?.charAt(0).toUpperCase() }}</span>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ activeSubPost.user?.name }}</p>
              <p class="text-xs text-gray-400">{{ formatDate(activeSubPost.created_at) }}</p>
            </div>
          </div>
          <button @click="activeSubPost = null" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
        </div>

        <!-- Caption + category — fixed -->
        <div class="px-5 pt-4 pb-2 shrink-0">
          <div class="flex gap-2 mb-2 flex-wrap">
            <span class="inline-block bg-indigo-50 dark:bg-indigo-900/40 text-indigo-500 text-xs px-2.5 py-1 rounded-full">{{ activeSubPost.category }}</span>
            <span v-if="activeSubPost.post_type === 'palette'" class="inline-block bg-orange-50 dark:bg-orange-900/40 text-orange-500 text-xs px-2.5 py-1 rounded-full">🎨 Palette</span>
            <span v-else class="inline-block bg-teal-50 dark:bg-teal-900/40 text-teal-500 text-xs px-2.5 py-1 rounded-full">🎭 Creation</span>
          </div>
          <p v-if="activeSubPost.caption" class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed" style="white-space: pre-wrap; word-break: break-word;">{{ activeSubPost.caption }}</p>
        </div>

        <!-- Palette colors — fixed -->
        <div v-if="activeSubPost.colors && activeSubPost.colors.length" class="px-5 pb-3 shrink-0">
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Palette used</p>
          <div class="flex gap-1.5 flex-wrap">
            <div
              v-for="(color, i) in activeSubPost.colors" :key="i"
              class="w-8 h-8 rounded-lg cursor-pointer hover:scale-110 transition-transform"
              :style="{ backgroundColor: color }"
              :title="color"
              @click="copyHex(color)"
            ></div>
          </div>
          <p v-if="copiedHex" class="text-xs text-green-500 mt-1">✓ Copied {{ copiedHex }}</p>
        </div>

        <!-- Comments — scrollable flex-1 section -->
        <div class="flex-1 flex flex-col overflow-hidden border-t border-gray-100 dark:border-gray-700 px-5 py-3">
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-2 shrink-0">Comments ({{ subComments.length }})</p>

          <!-- Scrollable comment list -->
          <div class="flex-1 overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden flex flex-col gap-3 mb-3">
            <div
              v-for="comment in subComments" :key="comment.id"
              class="flex gap-2 group"
            >
              <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0">
                <img v-if="comment.user?.avatar" :src="getImageUrl(comment.user.avatar)" class="w-full h-full object-cover" />
                <span v-else>{{ comment.user?.name?.charAt(0).toUpperCase() }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl px-3 py-2">
                  <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ comment.user?.name }}</p>
                  <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5 break-words" style="white-space: pre-wrap; word-break: break-word;">{{ comment.content }}</p>
                </div>
                <div class="flex items-center gap-3 mt-1 px-1">
                  <button @click="toggleSubCommentLike(comment)" class="text-xs transition" :class="comment.liked_by_user ? 'text-red-500' : 'text-gray-400 hover:text-red-400'">
                    ❤️ {{ comment.likes_count }}
                  </button>
                  <button @click="subReplyTarget = comment; subReplyText = ''" class="text-xs text-gray-400 hover:text-indigo-500 transition">Reply</button>
                  <span class="text-xs text-gray-400 ml-auto">{{ formatDate(comment.created_at) }}</span>
                </div>

                <!-- Replies -->
                <div v-if="comment.replies && comment.replies.length" class="mt-2 ml-3 flex flex-col gap-2">
                  <div v-for="reply in comment.replies" :key="reply.id" class="flex gap-2">
                    <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0">
                      <img v-if="reply.user?.avatar" :src="getImageUrl(reply.user.avatar)" class="w-full h-full object-cover" />
                      <span v-else>{{ reply.user?.name?.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="bg-gray-50 dark:bg-gray-700 rounded-xl px-3 py-2">
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ reply.user?.name }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5 break-words" style="white-space: pre-wrap; word-break: break-word;">{{ reply.content }}</p>
                      </div>
                      <div class="flex items-center gap-3 mt-1 px-1">
                        <button @click="toggleSubCommentLike(reply)" class="text-xs transition" :class="reply.liked_by_user ? 'text-red-500' : 'text-gray-400 hover:text-red-400'">❤️ {{ reply.likes_count }}</button>
                        <span class="text-xs text-gray-400 ml-auto">{{ formatDate(reply.created_at) }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Reply input -->
                <div v-if="subReplyTarget?.id === comment.id" class="mt-2 ml-3 flex gap-2">
                  <input v-model="subReplyText" type="text" :placeholder="`Reply to ${comment.user?.name}...`"
                    class="flex-1 text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2 focus:outline-none focus:border-indigo-400 transition"
                    @keyup.enter="submitSubReply(comment.id)" />
                  <button @click="submitSubReply(comment.id)" class="px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs hover:bg-indigo-500 transition">Reply</button>
                  <button @click="subReplyTarget = null" class="text-xs text-gray-400 hover:text-gray-600">✕</button>
                </div>
              </div>
            </div>

            <p v-if="!subComments.length" class="text-xs text-gray-400 text-center py-4">No comments yet.</p>
          </div>

          <!-- Comment input — fixed at bottom of comments section -->
          <div class="flex gap-2 pt-2 border-t border-gray-100 dark:border-gray-700 shrink-0">
            <input
              ref="subCommentInputRef"
              v-model="newSubComment"
              type="text"
              placeholder="Write a comment..."
              class="sub-comment-input flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2 focus:outline-none focus:border-indigo-400 transition"
              @keyup.enter="submitSubComment"
            />
            <button @click="submitSubComment" :disabled="!newSubComment.trim()" class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-sm disabled:opacity-40 hover:bg-indigo-500 transition">Post</button>
          </div>
        </div>

        <!-- Actions bar — always fixed at bottom -->
        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between shrink-0 bg-white dark:bg-gray-800">
          <div class="flex items-center gap-4">
            <!-- Like -->
            <button @click="toggleSubLike(activeSubPost)" class="flex items-center gap-1.5 text-sm transition" :class="activeSubPost.liked_by_user ? 'text-red-500' : 'text-gray-400 hover:text-red-400'">
              <span class="text-lg">{{ activeSubPost.liked_by_user ? '❤️' : '🤍' }}</span>
              <span>{{ activeSubPost.likes_count }}</span>
            </button>

            <!-- Comment bubble — focuses the input -->
            <button @click="focusSubComment" class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-indigo-500 transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
              </svg>
              <span>{{ subComments.length }}</span>
            </button>

            <!-- Save -->
            <button @click="toggleSubSave(activeSubPost)" class="flex items-center gap-1.5 text-sm transition" :class="activeSubPost.saved_by_user ? 'text-indigo-500' : 'text-gray-400 hover:text-indigo-400'">
              <svg class="w-5 h-5" :fill="activeSubPost.saved_by_user ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
              </svg>
              <span>{{ activeSubPost.saved_by_user ? 'Saved' : 'Save' }}</span>
            </button>
          </div>

          <button @click="openSubReport" class="text-xs text-gray-400 hover:text-gray-600 transition">Report</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Sub-post report modal -->
  <div v-if="subReportTarget" class="fixed inset-0 z-[80] bg-black/50 flex items-center justify-center px-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm p-6 flex flex-col gap-4">
      <h2 class="text-base font-semibold text-gray-800 dark:text-white">Report Post</h2>
      <div class="flex flex-col gap-2">
        <label v-for="topic in reportTopics" :key="topic.value" class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
          :class="subReport.topic === topic.value ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-400'">
          <input type="radio" v-model="subReport.topic" :value="topic.value" class="hidden" />
          <span>{{ topic.icon }}</span>
          <p class="text-sm text-gray-700 dark:text-gray-200">{{ topic.label }}</p>
        </label>
      </div>
      <textarea v-model="subReport.details" placeholder="Details (optional)" rows="2"
        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none resize-none" style="white-space: pre-wrap; word-break: break-word;"></textarea>
      <p v-if="subReportMsg" class="text-xs" :class="subReportMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ subReportMsg }}</p>
      <div class="flex gap-3">
        <button @click="subReportTarget = null" class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 transition">Cancel</button>
        <button @click="submitSubReport" :disabled="!subReport.topic" class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium disabled:opacity-40 transition">Report</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import axios from 'axios'
import { useAuth } from '../composables/useAuth'

const { user: authUser } = useAuth()
const currentUserId = computed(() => authUser.value?.id)

function getImageUrl(path) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

// Follow
const isFollowing   = ref(false)
const followLoading = ref(false)

// Followers/Following modals
const showFollowers     = ref(false)
const showFollowing     = ref(false)
const followersList     = ref([])
const followingList     = ref([])
const followListLoading = ref(false)

const props = defineProps({ userId: { type: Number, required: true } })
defineEmits(['close'])

const loading = ref(true)
const profile = ref(null)
const tab = ref('posts')

// Sub-post modal
const activeSubPost = ref(null)
const subComments   = ref([])
const newSubComment = ref('')
const subReplyTarget = ref(null)
const subReplyText   = ref('')
const copiedHex      = ref('')

// Sub-post report
const subReportTarget = ref(null)
const subReport       = ref({ topic: '', details: '' })
const subReportMsg    = ref('')

const subCommentInputRef = ref(null)

const reportTopics = [
  { value: 'spam',          icon: '📢', label: 'Spam' },
  { value: 'inappropriate', icon: '🚫', label: 'Inappropriate' },
  { value: 'harassment',    icon: '😡', label: 'Harassment' },
  { value: 'copyright',     icon: '©️',  label: 'Copyright' },
  { value: 'other',         icon: '❓', label: 'Other' },
]

const totalLikes = computed(() => (profile.value?.posts || []).reduce((s, p) => s + (p.likes_count || 0), 0))
const totalSaves = computed(() => (profile.value?.posts || []).reduce((s, p) => s + (p.saves_count || 0), 0))

onMounted(async () => {
  try {
    const { data } = await axios.get(`/api/users/${props.userId}/profile`)
    profile.value = data
    isFollowing.value = data.user?.is_following || false
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})

async function toggleFollow() {
  if (!profile.value) return
  followLoading.value = true
  try {
    const { data } = await axios.post(`/api/users/${profile.value.user.id}/follow`)
    isFollowing.value = data.following
    profile.value.user.followers_count = data.followers_count
  } catch (e) { console.error(e) }
  finally { followLoading.value = false }
}

async function openFollowers() {
  showFollowers.value = true
  followListLoading.value = true
  try {
    const { data } = await axios.get(`/api/users/${profile.value.user.id}/followers`)
    followersList.value = data
  } catch (e) { console.error(e) }
  finally { followListLoading.value = false }
}

async function openFollowing() {
  showFollowing.value = true
  followListLoading.value = true
  try {
    const { data } = await axios.get(`/api/users/${profile.value.user.id}/following`)
    followingList.value = data
  } catch (e) { console.error(e) }
  finally { followListLoading.value = false }
}

async function openSubPost(post) {
  activeSubPost.value = { ...post }
  subComments.value = []
  subReplyTarget.value = null
  newSubComment.value = ''
  try {
    const { data } = await axios.get(`/api/posts/${post.id}/comments`)
    subComments.value = data
  } catch (e) { console.error(e) }
}

async function toggleSubLike(post) {
  try {
    const { data } = await axios.post(`/api/posts/${post.id}/like`)
    post.liked_by_user = data.liked ? 1 : 0
    post.likes_count = data.likes_count
    const p = profile.value?.posts?.find(p => p.id === post.id)
    if (p) { p.liked_by_user = post.liked_by_user; p.likes_count = post.likes_count }
  } catch (e) { console.error(e) }
}

async function toggleSubSave(post) {
  try {
    const { data } = await axios.post(`/api/posts/${post.id}/save`)
    post.saved_by_user = data.saved ? 1 : 0
    const p = profile.value?.posts?.find(p => p.id === post.id)
    if (p) p.saved_by_user = post.saved_by_user
  } catch (e) { console.error(e) }
}

async function submitSubComment() {
  if (!newSubComment.value.trim() || !activeSubPost.value) return
  try {
    const { data } = await axios.post(`/api/posts/${activeSubPost.value.id}/comments`, {
      content: newSubComment.value.trim()
    })
    subComments.value.unshift({ ...data, likes_count: 0, liked_by_user: 0, replies: [] })
    newSubComment.value = ''
  } catch (e) { console.error(e) }
}

async function submitSubReply(parentId) {
  if (!subReplyText.value.trim()) return
  try {
    const { data } = await axios.post(`/api/posts/${activeSubPost.value.id}/comments`, {
      content: subReplyText.value.trim(),
      parent_id: parentId,
    })
    const parent = subComments.value.find(c => c.id === parentId)
    if (parent) parent.replies = [...(parent.replies || []), { ...data, likes_count: 0, liked_by_user: 0 }]
    subReplyText.value = ''
    subReplyTarget.value = null
  } catch (e) { console.error(e) }
}

async function toggleSubCommentLike(comment) {
  try {
    const { data } = await axios.post(`/api/comments/${comment.id}/like`)
    comment.liked_by_user = data.liked ? 1 : 0
    comment.likes_count = data.likes_count
  } catch (e) { console.error(e) }
}

async function copyHex(hex) {
  await navigator.clipboard.writeText(hex)
  copiedHex.value = hex
  setTimeout(() => copiedHex.value = '', 2000)
}

function openSubReport() {
  subReportTarget.value = activeSubPost.value
  subReport.value = { topic: '', details: '' }
  subReportMsg.value = ''
}

async function submitSubReport() {
  try {
    await axios.post(`/api/posts/${subReportTarget.value.id}/report`, subReport.value)
    subReportMsg.value = '✓ Reported!'
    setTimeout(() => { subReportTarget.value = null }, 2000)
  } catch (e) { subReportMsg.value = 'Failed.' }
}

function focusSubComment() {
  nextTick(() => {
    if (subCommentInputRef.value) subCommentInputRef.value.focus()
    else {
      const input = document.querySelector('.sub-comment-input')
      if (input) input.focus()
    }
  })
}

function roleColor(role) {
  if (role === 'superadmin') return 'bg-red-500'
  if (role === 'admin') return 'bg-blue-500'
  return 'bg-green-500'
}

function roleLabelClass(role) {
  if (role === 'superadmin') return 'text-red-400'
  if (role === 'admin') return 'text-blue-400'
  return 'text-green-400'
}

function formatDate(iso) {
  if (!iso) return ''
  const d = new Date(iso), now = new Date()
  const diff = Math.floor((now - d) / 1000)
  if (diff < 60) return 'just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

function formatDateLong(iso) {
  if (!iso) return 'N/A'
  return new Date(iso).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
}
</script>