<template>
  <!-- Community.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <!-- Header -->
    <div class="bg-[#0d1117] pt-8 pb-6 px-8">
      <div class="max-w-7xl mx-auto flex items-center justify-between flex-wrap gap-4">
        <div>
          <h1 class="text-white text-2xl font-bold">🌍 Community</h1>
          <p class="text-gray-400 text-xs mt-0.5">Share your art and discover color palettes</p>
        </div>

        <!-- Search + toggle -->
        <div class="flex gap-2 items-center">
          <!-- Post / Person toggle -->
          <div class="flex bg-white/10 rounded-xl p-1 border border-white/10">
            <button
              @click="searchType = 'posts'; fetchPosts()"
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition"
              :class="searchType === 'posts' ? 'bg-white text-gray-900' : 'text-gray-400 hover:text-white'"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
              </svg>
              Posts
            </button>
            <button
              @click="searchType = 'people'; fetchPosts()"
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition"
              :class="searchType === 'people' ? 'bg-white text-gray-900' : 'text-gray-400 hover:text-white'"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              People
            </button>
          </div>

          <div class="flex bg-white/10 rounded-xl overflow-hidden border border-white/10">
            <input
              v-model="search"
              type="text"
              :placeholder="searchType === 'posts' ? 'Search posts...' : 'Search people...'"
              class="bg-transparent px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none w-48"
              @keyup.enter="fetchPosts"
            />
            <button @click="fetchPosts" class="px-3 text-gray-400 hover:text-white transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </button>
          </div>

          <!-- Sort -->
          <div class="flex bg-white/10 rounded-xl p-1 border border-white/10">
            <button @click="sort = 'latest'; fetchPosts()" class="px-3 py-1.5 rounded-lg text-xs font-medium transition" :class="sort === 'latest' ? 'bg-white text-gray-900' : 'text-gray-400 hover:text-white'">🕐 Latest</button>
            <button @click="sort = 'popular'; fetchPosts()" class="px-3 py-1.5 rounded-lg text-xs font-medium transition" :class="sort === 'popular' ? 'bg-white text-gray-900' : 'text-gray-400 hover:text-white'">🔥 Popular</button>
          </div>

          <button
            @click="showCreateModal = true"
            class="px-4 py-2 rounded-xl text-white text-sm font-medium transition flex items-center gap-1.5"
            style="background: #8c2eff"
          >
            <span>+</span> Post
          </button>
        </div>
      </div>
    </div>

    <!-- Body: sidebar + content -->
    <div class="max-w-7xl mx-auto px-8 py-6 flex gap-6">

      <!-- Left sidebar -->
      <div class="w-52 shrink-0 flex flex-col gap-4">

        <!-- Post Type -->
        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest font-medium px-1 mb-2">Post Type</p>
          <div class="flex flex-col gap-1.5">
            <button
              v-for="pt in postTypes" :key="pt.value"
              @click="activePostType = pt.value; page = 1; fetchPosts()"
              class="w-full text-left px-4 py-2.5 rounded-xl border text-sm font-medium transition flex items-center gap-2"
              :class="activePostType === pt.value
                ? 'bg-indigo-600 text-white border-indigo-600'
                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-indigo-300'"
            >
              <span>{{ pt.icon }}</span><span>{{ pt.label }}</span>
            </button>
          </div>
        </div>

        <!-- Categories -->
        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest font-medium px-1 mb-2">Categories</p>
          <div class="flex flex-col gap-1.5">
            <button
              v-for="cat in categories" :key="cat.value"
              @click="activeCategory = cat.value; page = 1; fetchPosts()"
              class="w-full text-left px-4 py-2.5 rounded-xl border text-sm font-medium transition flex items-center gap-2"
              :class="activeCategory === cat.value
                ? 'bg-gray-800 dark:bg-white text-white dark:text-gray-900 border-gray-800 dark:border-white'
                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-gray-400'"
            >
              <span>{{ cat.icon }}</span><span>{{ cat.label }}</span>
            </button>
          </div>
        </div>

      </div>

      <!-- Right content -->
      <div class="flex-1 min-w-0">

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-24">
          <div class="w-8 h-8 border-2 border-gray-300 border-t-indigo-600 rounded-full animate-spin"></div>
        </div>

        <!-- People results -->
        <div v-else-if="searchType === 'people'">
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div
              v-for="person in people" :key="person.id"
              @click="selectedUserId = person.id"
              class="bg-white dark:bg-gray-800 rounded-2xl p-5 flex flex-col items-center gap-3 border border-gray-100 dark:border-gray-700 shadow-sm cursor-pointer hover:shadow-md hover:border-indigo-200 transition"
            >
              <div class="w-16 h-16 rounded-full bg-indigo-600 flex items-center justify-center text-white text-2xl font-bold overflow-hidden">
                <img v-if="person.avatar" :src="getImageUrl(person.avatar)" class="w-full h-full object-cover" />
                <span v-else>{{ person.name?.charAt(0).toUpperCase() }}</span>
              </div>
              <div class="text-center">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ person.name }}</p>
                <p class="text-xs text-gray-400">{{ person.posts_count }} posts</p>
                <div class="flex items-center justify-center gap-1 mt-1">
                  <span class="w-1.5 h-1.5 rounded-full" :class="person.role === 'superadmin' ? 'bg-red-500' : person.role === 'admin' ? 'bg-blue-500' : 'bg-green-500'"></span>
                  <span class="text-xs text-gray-400">{{ person.role }}</span>
                </div>
              </div>
            </div>
          </div>
          <p v-if="people.length === 0" class="text-center text-gray-400 py-12">No people found</p>
        </div>

        <!-- Posts grid -->
        <div v-else>
          <p v-if="posts.length === 0 && !loading" class="flex flex-col items-center justify-center py-24 gap-3 text-gray-400">
            <span class="text-4xl">🎨</span>
            <span class="text-sm">No posts yet. Be the first to share!</span>
          </p>

          <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div
              v-for="post in posts" :key="post.id"
              @click="openPostModal(post)"
              class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition group cursor-pointer"
            >
              <!-- Image -->
              <div class="relative overflow-hidden aspect-square bg-gray-100 dark:bg-gray-700">
                <img :src="getImageUrl(post.image)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                <span class="absolute top-2 left-2 bg-black/50 text-white text-xs px-2 py-0.5 rounded-full backdrop-blur-sm">{{ post.category }}</span>
                <button
                  v-if="canDelete(post)"
                  @click.stop="confirmDeletePost(post)"
                  class="absolute top-2 right-2 bg-red-500/80 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition"
                >✕</button>
              </div>

              <!-- Color palette strip -->
              <div v-if="post.colors && post.colors.length" class="flex h-4">
                <div v-for="(color, ci) in post.colors.slice(0, 8)" :key="ci" class="flex-1" :style="{ backgroundColor: color }"></div>
              </div>

              <!-- Mini info -->
              <div class="p-3 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                  <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0">
                    <img v-if="post.user?.avatar" :src="getImageUrl(post.user.avatar)" class="w-full h-full object-cover" />
                    <span v-else>{{ post.user?.name?.charAt(0).toUpperCase() }}</span>
                  </div>
                  <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ post.user?.name }}</p>
                </div>
                <div class="flex items-center gap-1 text-xs text-gray-400 shrink-0">
                  <span>{{ post.liked_by_user ? '❤️' : '🤍' }}</span>
                  <span>{{ post.likes_count }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="pagination && pagination.last_page > 1" class="flex items-center justify-center gap-3 mt-8">
            <button @click="page--; fetchPosts()" :disabled="page <= 1" class="px-4 py-2 rounded-full border border-gray-200 dark:border-gray-600 text-sm text-gray-500 disabled:opacity-40 hover:border-gray-400 transition">← Prev</button>
            <span class="text-sm text-gray-400">{{ page }} / {{ pagination.last_page }}</span>
            <button @click="page++; fetchPosts()" :disabled="page >= pagination.last_page" class="px-4 py-2 rounded-full border border-gray-200 dark:border-gray-600 text-sm text-gray-500 disabled:opacity-40 hover:border-gray-400 transition">Next →</button>
          </div>
        </div>

      </div>
    </div>

    <!-- ─── Post View Modal ─── -->
    <div v-if="activePost" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center px-4" @click.self="activePost = null">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex overflow-hidden">

        <!-- Left: image -->
        <div class="w-1/2 bg-black flex items-center justify-center shrink-0">
          <img :src="getImageUrl(activePost.image)" class="w-full h-full object-contain max-h-[90vh]" />
        </div>

        <!-- Right: details -->
        <div class="flex-1 overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden flex flex-col min-h-0">

          <!-- User header -->
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700 shrink-0">
            <div
              class="flex items-center gap-3 cursor-pointer"
              @click="selectedUserId = activePost.user?.id; activePost = null"
            >
              <div class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold overflow-hidden">
                <img v-if="activePost.user?.avatar" :src="getImageUrl(activePost.user.avatar)" class="w-full h-full object-cover" />
                <span v-else>{{ activePost.user?.name?.charAt(0).toUpperCase() }}</span>
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 hover:text-indigo-500 transition">{{ activePost.user?.name }}</p>
                <p class="text-xs text-gray-400">{{ formatDate(activePost.created_at) }}</p>
              </div>
            </div>
            <button @click="activePost = null" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
          </div>

          <!-- Category + caption -->
          <div class="px-5 py-4 shrink-0">
            <span class="inline-block bg-indigo-50 dark:bg-indigo-900/40 text-indigo-500 text-xs px-2.5 py-1 rounded-full">{{ activePost.category }}</span>
            <span v-if="activePost.post_type === 'palette'" class="inline-block bg-orange-50 dark:bg-orange-900/40 text-orange-500 text-xs px-2.5 py-1 rounded-full ml-1">🎨 Palette</span>
            <span v-else class="inline-block bg-teal-50 dark:bg-teal-900/40 text-teal-500 text-xs px-2.5 py-1 rounded-full ml-1">🎭 Creation</span>
            <p v-if="activePost.caption" class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed" style="white-space: pre-wrap; word-break: break-word;">{{ activePost.caption }}</p>
          </div>

          <!-- Color palette -->
          <div v-if="activePost.colors && activePost.colors.length" class="px-5 pb-4 shrink-0">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Palette used</p>
            <div class="flex gap-2 flex-wrap">
              <div
                v-for="(color, i) in activePost.colors" :key="i"
                class="w-10 h-10 rounded-lg cursor-pointer hover:scale-110 transition-transform"
                :style="{ backgroundColor: color }"
                :title="color"
                @click="copyHex(color)"
              ></div>
            </div>
            <p v-if="copiedHex" class="text-xs text-green-500 mt-1">✓ Copied {{ copiedHex }}</p>
          </div>

          <!-- Comments section — inside post modal right details panel -->
          <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 flex flex-col flex-1 min-h-0" style="min-height: 200px;">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-3 shrink-0">Comments ({{ comments.length }})</p>

            <!-- Comment list -->
            <div class="flex-1 overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden flex flex-col gap-3 mb-3">
              <div
                v-for="(comment, ci) in comments" :key="comment.id"
                class="flex flex-col gap-1 animate-fade-in-up"
                :style="{ animationDelay: `${ci * 0.05}s` }"
              >
                <!-- Main comment -->
                <div class="flex gap-2 group">
                  <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0 cursor-pointer" @click="openUserProfile(comment.user)">
                    <img v-if="comment.user?.avatar" :src="getImageUrl(comment.user.avatar)" class="w-full h-full object-cover" />
                    <span v-else>{{ comment.user?.name?.charAt(0).toUpperCase() }}</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl px-3 py-2">
                      <p class="text-xs font-semibold text-gray-700 dark:text-gray-200 cursor-pointer hover:text-indigo-500 transition" @click="openUserProfile(comment.user)">{{ comment.user?.name }}</p>
                      <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5" style="white-space: pre-wrap; word-break: break-word;">{{ comment.content }}</p>
                    </div>
                    <div class="flex items-center gap-3 mt-1 px-1">
                      <button @click="toggleCommentLike(comment)" class="text-xs transition" :class="comment.liked_by_user ? 'text-red-500' : 'text-gray-400 hover:text-red-400'">
                        ❤️ {{ comment.likes_count }}
                      </button>
                      <button @click="replyTarget = comment; replyText = ''" class="text-xs text-gray-400 hover:text-indigo-500 transition">Reply</button>
                      <button @click="openCommentReport(comment)" class="text-xs text-gray-400 hover:text-gray-600 transition">Report</button>
                      <button v-if="comment.user_id === user?.id || isAdmin()" @click="deleteComment(comment.id)" class="text-xs text-red-400 hover:text-red-600 transition opacity-0 group-hover:opacity-100">Delete</button>
                      <span class="text-xs text-gray-400 ml-auto">{{ formatDate(comment.created_at) }}</span>
                    </div>

                    <!-- Replies -->
                    <div v-if="comment.replies && comment.replies.length" class="mt-2 ml-4 flex flex-col gap-2">
                      <div v-for="reply in comment.replies" :key="reply.id" class="flex gap-2 group">
                        <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0 cursor-pointer" @click="openUserProfile(reply.user)">
                          <img v-if="reply.user?.avatar" :src="getImageUrl(reply.user.avatar)" class="w-full h-full object-cover" />
                          <span v-else>{{ reply.user?.name?.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl px-3 py-2">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-200 cursor-pointer hover:text-indigo-500 transition" @click="openUserProfile(reply.user)">{{ reply.user?.name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5" style="white-space: pre-wrap; word-break: break-word;">{{ reply.content }}</p>
                          </div>
                          <div class="flex items-center gap-3 mt-1 px-1">
                            <button @click="toggleCommentLike(reply)" class="text-xs transition" :class="reply.liked_by_user ? 'text-red-500' : 'text-gray-400 hover:text-red-400'">❤️ {{ reply.likes_count }}</button>
                            <button v-if="reply.user_id === user?.id || isAdmin()" @click="deleteComment(reply.id)" class="text-xs text-red-400 hover:text-red-600 transition opacity-0 group-hover:opacity-100">Delete</button>
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
                      <button @click="submitReply(comment.id)" class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-xs transition hover:bg-indigo-500">Reply</button>
                      <button @click="replyTarget = null" class="text-xs text-gray-400 hover:text-gray-600">✕</button>
                    </div>
                  </div>
                </div>
              </div>

              <p v-if="!comments.length" class="text-xs text-gray-400 text-center py-3">No comments yet. Be the first!</p>
            </div>

            <!-- New comment input -->
            <div class="flex gap-2 pt-2 border-t border-gray-100 dark:border-gray-700 shrink-0">
              <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0">
                <img v-if="user?.avatar" :src="user.avatar" class="w-full h-full object-cover" />
                <span v-else>{{ user?.name?.charAt(0).toUpperCase() }}</span>
              </div>
              <div class="flex-1 flex gap-2">
                <input v-model="newComment" type="text" placeholder="Write a comment..."
                  class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2 focus:outline-none focus:border-indigo-400 transition"
                  @keyup.enter="submitComment" />
                <button @click="submitComment" :disabled="!newComment.trim()" class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-sm disabled:opacity-40 transition hover:bg-indigo-500">Post</button>
              </div>
            </div>
          </div>

          <!-- Comment Report Modal -->
          <div v-if="reportCommentTarget" class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center px-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg flex flex-col overflow-hidden">

              <!-- Header -->
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                  Report Comment
                </h2>
              </div>

              <!-- Body -->
              <div class="p-6 flex flex-col gap-4 overflow-y-auto">

                <!-- Topics -->
                <div class="flex flex-col gap-2">
                  <label
                    v-for="topic in reportTopics"
                    :key="topic.value"
                    class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                    :class="commentReport.topic === topic.value
                      ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-900/30'
                      : 'border-gray-200 dark:border-gray-600 hover:border-gray-400'"
                  >
                    <input type="radio" v-model="commentReport.topic" :value="topic.value" class="hidden" />
                    <span>{{ topic.icon }}</span>
                    <p class="text-sm text-gray-700 dark:text-gray-200">
                      {{ topic.label }}
                    </p>
                  </label>
                </div>

                <!-- Textarea -->
                <textarea
                  v-model="commentReport.details"
                  placeholder="Add more details (optional)"
                  rows="3"
                  class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none resize-none [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                  style="white-space: pre-wrap; word-break: break-word;"
                ></textarea>

                <!-- Message -->
                <p
                  v-if="commentReportMsg"
                  class="text-xs"
                  :class="commentReportMsg.includes('✓') ? 'text-green-500' : 'text-red-500'"
                >
                  {{ commentReportMsg }}
                </p>
              </div>

              <!-- Footer -->
              <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                <button
                  @click="reportCommentTarget = null"
                  class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                >
                  Cancel
                </button>

                <button
                  @click="submitCommentReport"
                  :disabled="!commentReport.topic || commentReporting"
                  class="flex-1 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-medium disabled:opacity-40 transition"
                >
                  Report
                </button>
              </div>

            </div>
          </div>

          <!-- User Profile Modal -->
          <UserProfileModal v-if="selectedUserId" :userId="selectedUserId" @close="selectedUserId = null" />

          <!-- Actions -->
          <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-4">
              <!-- Like -->
              <button @click="toggleLike(activePost)" class="flex items-center gap-1.5 text-sm transition" :class="activePost.liked_by_user ? 'text-red-500' : 'text-gray-400 hover:text-red-400'">
                <span class="text-lg">{{ activePost.liked_by_user ? '❤️' : '🤍' }}</span>
                <span>{{ activePost.likes_count }}</span>
              </button>

              <!-- Save post -->
              <button @click="toggleSavePost(activePost)" class="flex items-center gap-1.5 text-sm transition" :class="activePost.saved_by_user ? 'text-indigo-500' : 'text-gray-400 hover:text-indigo-400'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
                <span>{{ activePost.saved_by_user ? 'Saved' : 'Save' }}</span>
              </button>
            </div>

            <div class="flex items-center gap-3">
              <!-- Report -->
              <button @click="openReport(activePost)" class="text-xs text-gray-400 hover:text-gray-600 transition">Report</button>

              <!-- Edit -->
              <button
                v-if="activePost.user_id === user?.id"
                @click="openEditModal(activePost)"
                class="text-xs text-indigo-400 hover:text-indigo-600 border border-indigo-200 px-3 py-1 rounded-full transition"
              >
                Edit
              </button>

              <!-- Delete -->
              <button v-if="canDelete(activePost)" @click="confirmDeletePost(activePost)" class="text-xs text-red-400 hover:text-red-600 border border-red-200 px-3 py-1 rounded-full transition">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── User Profile Modal (shared component) ─── -->
    <UserProfileModal
      v-if="selectedUserId"
      :userId="selectedUserId"
      @close="selectedUserId = null"
    />

    <!-- ─── Create Post Modal ─── -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center px-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg flex flex-col gap-4 p-6 max-h-[90vh] overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-800 dark:text-white">Share your art</h2>
          <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
        </div>

        <!-- Post type -->
        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Post type</p>
          <div class="flex gap-2">
            <button
              @click="newPost.postType = 'creation'"
              class="flex-1 py-2.5 rounded-xl border text-sm font-medium transition"
              :class="newPost.postType === 'creation' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:border-indigo-400'"
            >
              🎭 Creation
            </button>
            <button
              @click="newPost.postType = 'palette'; newPost.category = 'Palette'"
              class="flex-1 py-2.5 rounded-xl border text-sm font-medium transition"
              :class="newPost.postType === 'palette' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:border-indigo-400'"
            >
              🎨 Palette
            </button>
          </div>
        </div>

        <!-- Image upload -->
        <div class="flex justify-center">
          <div
            class="relative border-2 border-dashed rounded-xl overflow-hidden cursor-pointer hover:border-indigo-400 transition shrink-0"
            :class="newPost.previewUrl ? 'border-indigo-400' : 'border-gray-200 dark:border-gray-600'"
            style="aspect-ratio: 1 / 1; width: min(100%, 280px);"
            @click="triggerImageUpload"
            @dragover.prevent
            @drop.prevent="onImageDrop"
          >
            <input ref="imageInput" type="file" accept="image/*" class="hidden" @change="onImageChange" />
            <img v-if="newPost.previewUrl" :src="newPost.previewUrl" class="absolute inset-0 w-full h-full object-cover" />
            <div v-else class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-gray-400">
              <span class="text-4xl">🖼</span>
              <p class="text-sm text-center px-4">{{ newPost.postType === 'palette' ? 'Add image (optional)' : 'Click or drag & drop your art' }}</p>
              <p class="text-xs text-gray-300">Max 5MB</p>
            </div>
          </div>
        </div>

        <!-- Caption -->
        <textarea
          v-model="newPost.caption"
          placeholder="Write a caption... (optional)"
          rows="2"
          @input="autoResize($event)"
          class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-400 transition resize-none overflow-hidden"
          style="min-height: 70px; max-height: 200px; white-space: pre-wrap; word-break: break-word;"
        ></textarea>

        <!-- Category -->
        <select
          v-if="newPost.postType === 'creation'"
          v-model="newPost.category"
          class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 transition"
        >
          <option v-for="cat in postCategories" :key="cat" :value="cat">{{ cat }}</option>
        </select>

        <!-- Palette picker dropdown -->
        <div>
          <p class="text-xs text-gray-400 mb-2 uppercase tracking-widest">{{ newPost.postType === 'palette' ? 'Palette (required)' : 'Attach palette (optional)' }}</p>

          <!-- Selected preview -->
          <div v-if="newPost.colors.length" class="flex gap-1 h-8 rounded-lg overflow-hidden mb-2 border border-gray-200 dark:border-gray-600">
            <div v-for="(c, i) in newPost.colors" :key="i" class="flex-1" :style="{ backgroundColor: c }"></div>
          </div>

          <!-- Dropdown -->
          <div class="relative" ref="palettDropRef">
            <button
              @click="paletteDropOpen = !paletteDropOpen"
              class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 text-sm text-gray-500 dark:text-gray-300 hover:border-indigo-400 transition"
            >
              <span>{{ newPost.colors.length ? `${newPost.colors.length} colors selected` : 'Select a palette...' }}</span>
              <svg class="w-4 h-4 transition-transform" :class="paletteDropOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div v-if="paletteDropOpen" class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl overflow-hidden max-h-48 overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
              <div @click="newPost.colors = []; paletteDropOpen = false" class="px-4 py-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm text-gray-400 border-b border-gray-100 dark:border-gray-700">
                None
              </div>
              <div
                v-for="palette in savedPalettes" :key="palette.id"
                @click="newPost.colors = palette.colors; paletteDropOpen = false"
                class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition border-b border-gray-100 dark:border-gray-700 last:border-0"
              >
                <div class="flex gap-0.5 h-6 rounded overflow-hidden w-24 shrink-0">
                  <div v-for="(c, ci) in palette.colors" :key="ci" class="flex-1" :style="{ backgroundColor: c }"></div>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-300 truncate">{{ palette.name }}</p>
              </div>
            </div>
          </div>
        </div>

        <p v-if="createError" class="text-xs text-red-500">{{ createError }}</p>

        <div class="flex gap-3">
          <button @click="showCreateModal = false" class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 hover:border-gray-400 transition">Cancel</button>
          <button
            @click="createPost"
            :disabled="(newPost.postType === 'creation' && !newPost.file) || (newPost.postType === 'palette' && !newPost.colors.length) || creating"
            class="flex-1 py-2.5 rounded-xl text-white text-sm font-medium disabled:opacity-40 transition"
            style="background: linear-gradient(to right, #4f46e5, #f97316)"
          >
            {{ creating ? 'Posting...' : 'Post' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ─── Report Modal ─── -->
    <div v-if="reportTarget" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center px-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm p-6 flex flex-col gap-4 max-h-[200vh] overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white">Report Post</h2>
        <div class="flex flex-col gap-2">
          <label
            v-for="topic in reportTopics" :key="topic.value"
            class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
            :class="report.topic === topic.value ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-400'"
          >
            <input type="radio" v-model="report.topic" :value="topic.value" class="hidden" />
            <span>{{ topic.icon }}</span>
            <div>
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ topic.label }}</p>
              <p class="text-xs text-gray-400">{{ topic.desc }}</p>
            </div>
          </label>
        </div>
        <textarea v-model="report.details" placeholder="Additional details (optional)" rows="2" class="w-full height-auto border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none resize-none [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" style="white-space: pre-wrap; word-break: break-word;"></textarea>
        <p v-if="reportMsg" class="text-xs" :class="reportMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ reportMsg }}</p>
        <div class="flex gap-3">
          <button @click="reportTarget = null; report = { topic: '', details: '' }" class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 transition">Cancel</button>
          <button @click="submitReport" :disabled="!report.topic || reporting" class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium disabled:opacity-40 transition">{{ reporting ? 'Reporting...' : 'Submit' }}</button>
        </div>
      </div>
    </div>

    <!-- ─── Delete Confirm Modal ─── -->
    <div v-if="deleteTarget" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center px-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-sm">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-2">Delete post?</h2>
        <p class="text-sm text-gray-400 mb-6">This will permanently delete the post and image.</p>
        <div class="flex gap-3">
          <button @click="deleteTarget = null" class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 transition">Cancel</button>
          <button @click="deletePost" class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium transition">Delete</button>
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

        <textarea
          v-model="editForm.caption"
          placeholder="Caption..."
          rows="3"
          @input="autoResize($event)"
          class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 text-sm focus:outline-none resize-none transition"
          style="min-height: 80px; max-height: 200px;"
        ></textarea>

        <select
          v-if="editTarget.post_type !== 'palette'"
          v-model="editForm.category"
          class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none transition"
        >
          <option v-for="cat in postCategories" :key="cat" :value="cat">{{ cat }}</option>
        </select>

        <p v-if="editMsg" class="text-xs" :class="editMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ editMsg }}</p>

        <div class="flex gap-3">
          <button @click="editTarget = null" class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 transition">Cancel</button>
          <button @click="saveEdit" :disabled="savingEdit" class="flex-1 py-2.5 rounded-xl text-white text-sm font-medium disabled:opacity-40 transition" style="background: linear-gradient(to right, #4f46e5, #f97316)">
            {{ savingEdit ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <div>
    <Footer @navigate="$emit('navigate', $event)" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuth } from '../composables/useAuth'
import { usePaletteStore } from '../composables/usePaletteStore'
import UserProfileModal from './UserProfileModal.vue'
import Footer from './Footer.vue'
const emit = defineEmits(['navigate'])

function getImageUrl(path) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `http://localhost:8000/storage/${path}`
}

const { user, isAdmin } = useAuth()
const { getAll } = usePaletteStore()

const posts = ref([])
const people = ref([])
const loading = ref(false)
const page = ref(1)
const pagination = ref(null)
const search = ref('')
const searchType = ref('posts')
const activeCategory = ref('all')
const sort = ref('latest')
const savedPalettes = ref([])
const copiedHex = ref('')

// Comments
const comments = ref([])
const newComment = ref('')
const replyTarget = ref(null)
const replyText = ref('')
const selectedUserId = ref(null)

// Comment report
const reportCommentTarget = ref(null)
const commentReport = ref({ topic: '', details: '' })
const commentReporting = ref(false)
const commentReportMsg = ref('')

// When opening a post modal, load comments
async function openPostModal(post) {
  activePost.value = { ...post }
  comments.value = []
  try {
    const { data } = await axios.get(`/api/posts/${post.id}/comments`)
    comments.value = data
  } catch (e) { console.error(e) }
}

function openUserProfile(user) {
  if (user?.id) selectedUserId.value = user.id
}

async function submitComment() {
  if (!newComment.value.trim() || !activePost.value) return
  try {
    const { data } = await axios.post(`/api/posts/${activePost.value.id}/comments`, { content: newComment.value.trim() })
    comments.value.unshift({ ...data, likes_count: 0, liked_by_user: 0, replies: [] })
    newComment.value = ''
  } catch (e) { console.error(e) }
}

async function submitReply(parentId) {
  if (!replyText.value.trim() || !activePost.value) return
  try {
    const { data } = await axios.post(`/api/posts/${activePost.value.id}/comments`, {
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
// Post modal
const activePost = ref(null)

// Create post
const showCreateModal = ref(false)
const imageInput = ref(null)
const creating = ref(false)
const createError = ref('')
const newPost = ref({ file: null, previewUrl: '', caption: '', category: 'Abstract', colors: [], postType: 'creation' })

// Palette picker dropdown
const paletteDropOpen = ref(false)
const palettDropRef = ref(null)

// Report
const reportTarget = ref(null)
const report = ref({ topic: '', details: '' })
const reporting = ref(false)
const reportMsg = ref('')

// Delete
const deleteTarget = ref(null)

// Edit
const editTarget = ref(null)
const editForm = ref({ caption: '', category: '' })
const editMsg = ref('')
const savingEdit = ref(false)

// Post Type filter
const activePostType = ref('all')
const postTypes = [
  { value: 'all',      icon: '✨', label: 'All' },
  { value: 'creation', icon: '🎭', label: 'Creation' },
  { value: 'palette',  icon: '🎨', label: 'Palette' },
]

const categories = [
  { value: 'all',          icon: '🌐', label: 'All' },
  { value: 'Abstract',     icon: '🌀', label: 'Abstract' },
  { value: 'Nature',       icon: '🌿', label: 'Nature' },
  { value: 'Portrait',     icon: '👤', label: 'Portrait' },
  { value: 'Architecture', icon: '🏛',  label: 'Architecture' },
  { value: 'Food',         icon: '🍜', label: 'Food' },
  { value: 'Fashion',      icon: '👗', label: 'Fashion' },
  { value: 'Digital Art',  icon: '💻', label: 'Digital Art' },
  { value: 'Photography',  icon: '📷', label: 'Photography' },
  { value: 'Other',        icon: '❓', label: 'Other' },
]

const postCategories = ['Abstract', 'Nature', 'Portrait', 'Architecture', 'Food', 'Fashion', 'Digital Art', 'Photography', 'Other']

const reportTopics = [
  { value: 'spam',          icon: '📢', label: 'Spam',          desc: 'Unwanted commercial content' },
  { value: 'inappropriate', icon: '🚫', label: 'Inappropriate', desc: 'Offensive or adult content' },
  { value: 'harassment',    icon: '😡', label: 'Harassment',    desc: 'Bullying or targeted attacks' },
  { value: 'copyright',     icon: '©️',  label: 'Copyright',     desc: 'Stolen content' },
  { value: 'other',         icon: '❓', label: 'Other',          desc: 'Something else' },
]

onMounted(async () => {
  await fetchPosts()
  savedPalettes.value = await getAll()
})

async function fetchPosts() {
  loading.value = true
  try {
    if (searchType.value === 'people') {
      const { data } = await axios.get(`/api/posts?type=people&search=${encodeURIComponent(search.value)}`)
      people.value = data.data || data
      return
    }
    const params = new URLSearchParams({ page: page.value, category: activeCategory.value, sort: sort.value })
    if (activePostType.value !== 'all') params.set('post_type', activePostType.value)
    if (search.value) params.set('search', search.value)
    const { data } = await axios.get(`/api/posts?${params}`)
    posts.value = data.data
    pagination.value = { last_page: data.last_page, total: data.total }
  } catch (e) {
    console.error('Failed to fetch:', e)
  } finally {
    loading.value = false
  }
}

function triggerImageUpload() { imageInput.value.click() }

function loadImageFile(file) {
  if (!file || !file.type.startsWith('image/')) return
  newPost.value.file = file
  const reader = new FileReader()
  reader.onload = e => { newPost.value.previewUrl = e.target.result }
  reader.readAsDataURL(file)
}

function onImageChange(e) { loadImageFile(e.target.files[0]) }
function onImageDrop(e) { loadImageFile(e.dataTransfer.files[0]) }

function autoResize(e) {
  e.target.style.height = 'auto'
  e.target.style.height = e.target.scrollHeight + 'px'
}

async function createPost() {
  creating.value = true
  createError.value = ''

  const finalCategory = newPost.value.postType === 'palette' ? 'Palette' : newPost.value.category

  try {
    const formData = new FormData()
    if (newPost.value.file) formData.append('image', newPost.value.file)
    if (newPost.value.caption) formData.append('caption', newPost.value.caption)
    formData.append('category', finalCategory)
    formData.append('post_type', newPost.value.postType)
    if (newPost.value.colors.length) {
      newPost.value.colors.forEach((c, i) => formData.append(`colors[${i}]`, c))
    }

    const { data } = await axios.post('/api/posts', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    posts.value.unshift({ ...data, liked_by_user: 0, likes_count: 0, saved_by_user: 0 })
    showCreateModal.value = false
    newPost.value = { file: null, previewUrl: '', caption: '', category: 'Abstract', colors: [], postType: 'creation' }
  } catch (e) {
    createError.value = e?.response?.data?.message || 'Failed to post.'
  } finally {
    creating.value = false
  }
}

function canDelete(post) {
  return post.user_id === user.value?.id || isAdmin()
}

function confirmDeletePost(post) {
  deleteTarget.value = post
  activePost.value = null
}

async function deletePost() {
  const isAdminDelete = deleteTarget.value.user_id !== user.value?.id
  const url = isAdminDelete ? `/api/admin/posts/${deleteTarget.value.id}` : `/api/posts/${deleteTarget.value.id}`
  try {
    await axios.delete(url)
    posts.value = posts.value.filter(p => p.id !== deleteTarget.value.id)
    deleteTarget.value = null
  } catch (e) { console.error(e) }
}

async function toggleLike(post) {
  try {
    const { data } = await axios.post(`/api/posts/${post.id}/like`)
    post.liked_by_user = data.liked ? 1 : 0
    post.likes_count = data.likes_count
    const listPost = posts.value.find(p => p.id === post.id)
    if (listPost) { listPost.liked_by_user = post.liked_by_user; listPost.likes_count = post.likes_count }
  } catch (e) { console.error(e) }
}

async function toggleSavePost(post) {
  try {
    const { data } = await axios.post(`/api/posts/${post.id}/save`)
    post.saved_by_user = data.saved ? 1 : 0
  } catch (e) { console.error(e) }
}

async function copyHex(hex) {
  await navigator.clipboard.writeText(hex)
  copiedHex.value = hex
  setTimeout(() => copiedHex.value = '', 2000)
}

function openReport(post) {
  reportTarget.value = post
  report.value = { topic: '', details: '' }
  reportMsg.value = ''
  activePost.value = null
}

async function submitReport() {
  if (!report.value.topic) return
  reporting.value = true
  reportMsg.value = ''
  try {
    await axios.post(`/api/posts/${reportTarget.value.id}/report`, report.value)
    reportMsg.value = '✓ Report submitted!'
    setTimeout(() => { reportTarget.value = null; report.value = { topic: '', details: '' } }, 2000)
  } catch (e) {
    reportMsg.value = e?.response?.data?.message || 'Failed to submit.'
  } finally {
    reporting.value = false
  }
}

function openEditModal(post) {
  editTarget.value = post
  editForm.value = { caption: post.caption || '', category: post.category || 'Other' }
  editMsg.value = ''
  activePost.value = null
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
    const idx = posts.value.findIndex(p => p.id === data.id)
    if (idx !== -1) posts.value[idx] = { ...posts.value[idx], ...data }
    editMsg.value = '✓ Post updated!'
    setTimeout(() => { editTarget.value = null; editMsg.value = '' }, 1500)
  } catch (e) {
    editMsg.value = e?.response?.data?.message || 'Failed to update.'
  } finally {
    savingEdit.value = false
  }
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