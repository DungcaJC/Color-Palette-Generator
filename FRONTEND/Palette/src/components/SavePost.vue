<template>
  <!-- SavePost.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300 p-8">
    <div class="max-w-5xl mx-auto flex flex-col gap-6">

      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">Saved Posts</h1>
          <p class="text-sm text-gray-400 mt-1">Posts you saved from the community</p>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center py-24">
        <div class="w-6 h-6 border-2 border-gray-300 border-t-indigo-600 rounded-full animate-spin"></div>
      </div>

      <div v-else-if="posts.length === 0" class="flex flex-col items-center justify-center py-24 gap-3 text-gray-300">
        <span class="text-4xl">🔖</span>
        <p class="text-sm">No saved posts yet. Save posts from the community!</p>
      </div>

      <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div
          v-for="post in posts" :key="post.id"
          @click="activePost = post"
          class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition cursor-pointer group"
        >
          <div class="relative aspect-square bg-gray-100 dark:bg-gray-700">
            <img :src="`http://localhost:8000/storage/${post.image}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            <span class="absolute top-2 left-2 bg-black/50 text-white text-xs px-2 py-0.5 rounded-full">{{ post.category }}</span>
          </div>
          <div v-if="post.colors && post.colors.length" class="flex h-4">
            <div v-for="(c, ci) in post.colors.slice(0, 8)" :key="ci" class="flex-1" :style="{ backgroundColor: c }"></div>
          </div>
          <div class="p-3 flex items-center justify-between">
            <div class="flex items-center gap-2 min-w-0">
              <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden shrink-0">
                <img v-if="post.user?.avatar" :src="`http://localhost:8000/storage/${post.user.avatar}`" class="w-full h-full object-cover" />
                <span v-else>{{ post.user?.name?.charAt(0).toUpperCase() }}</span>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ post.user?.name }}</p>
            </div>
            <span class="text-xs text-gray-400 shrink-0">{{ post.liked_by_user ? '❤️' : '🤍' }} {{ post.likes_count }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Post view modal (reuse same design) -->
    <div v-if="activePost" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center px-4" @click.self="activePost = null">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex overflow-hidden">
        <div class="w-1/2 bg-black flex items-center justify-center shrink-0">
          <img :src="`http://localhost:8000/storage/${activePost.image}`" class="w-full h-full object-contain max-h-[90vh]" />
        </div>
        <div class="flex-1 flex flex-col overflow-y-auto">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold overflow-hidden">
                <img v-if="activePost.user?.avatar" :src="`http://localhost:8000/storage/${activePost.user.avatar}`" class="w-full h-full object-cover" />
                <span v-else>{{ activePost.user?.name?.charAt(0).toUpperCase() }}</span>
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ activePost.user?.name }}</p>
                <p class="text-xs text-gray-400">{{ formatDate(activePost.created_at) }}</p>
              </div>
            </div>
            <button @click="activePost = null" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
          </div>
          <div class="px-5 py-4 flex-1">
            <span class="inline-block bg-indigo-50 dark:bg-indigo-900/40 text-indigo-500 text-xs px-2.5 py-1 rounded-full mb-3">{{ activePost.category }}</span>
            <p v-if="activePost.caption" class="text-sm text-gray-600 dark:text-gray-300">{{ activePost.caption }}</p>
          </div>
          <div v-if="activePost.colors && activePost.colors.length" class="px-5 pb-4">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Palette</p>
            <div class="flex gap-2 flex-wrap">
              <div v-for="(c, i) in activePost.colors" :key="i" class="w-10 h-10 rounded-lg" :style="{ backgroundColor: c }" :title="c"></div>
            </div>
          </div>
          <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <button @click="toggleLike(activePost)" class="flex items-center gap-1.5 text-sm transition" :class="activePost.liked_by_user ? 'text-red-500' : 'text-gray-400 hover:text-red-400'">
              <span class="text-lg">{{ activePost.liked_by_user ? '❤️' : '🤍' }}</span>
              <span>{{ activePost.likes_count }}</span>
            </button>
            <button @click="unsavePost(activePost)" class="text-xs text-indigo-500 hover:text-indigo-700 transition flex items-center gap-1">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
              Unsave
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const posts = ref([])
const loading = ref(true)
const activePost = ref(null)

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/saved-posts')
    posts.value = data
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})

async function toggleLike(post) {
  try {
    const { data } = await axios.post(`/api/posts/${post.id}/like`)
    post.liked_by_user = data.liked ? 1 : 0
    post.likes_count = data.likes_count
  } catch (e) { console.error(e) }
}

async function unsavePost(post) {
  try {
    await axios.post(`/api/posts/${post.id}/save`)
    posts.value = posts.value.filter(p => p.id !== post.id)
    activePost.value = null
  } catch (e) { console.error(e) }
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>