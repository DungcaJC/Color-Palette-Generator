<template>
  <!-- AdminAppeals.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <div class="bg-[#0d1117] pt-10 pb-20 px-8">
      <div class="max-w-6xl mx-auto">
        <div class="flex items-center gap-3 mb-1"><span class="text-2xl">📤</span><h1 class="text-white text-2xl font-semibold">Appeals</h1></div>
        <p class="text-gray-400 text-sm ml-9">Review user appeals for warnings</p>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
      
      <div class="flex gap-3 flex-wrap items-center mb-4">
        <!-- Search -->
        <input
          v-model="search"
          type="text"
          placeholder="Search by user name..."
          class="flex-1 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-400 transition min-w-48"
          @input="fetchAppeals"
        />

        <!-- Status tabs -->
        <div class="flex gap-1.5 flex-wrap">
          <button
            v-for="tab in tabs" :key="tab.value"
            @click="activeTab = tab.value; fetchAppeals()"
            class="px-3 py-2 rounded-xl text-xs font-medium border transition"
            :class="activeTab === tab.value
              ? 'bg-black dark:bg-white text-white dark:text-black border-black dark:border-white'
              : 'bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:border-gray-400'"
          >{{ tab.label }}</button>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center py-24">
        <div class="w-6 h-6 border-2 border-gray-300 border-t-indigo-600 rounded-full animate-spin"></div>
      </div>

      <div v-else class="flex flex-col gap-4">
        <div
          v-for="(appeal, i) in appeals" :key="appeal.id"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 animate-fade-in-up"
          :style="{ animationDelay: `${i * 0.05}s` }"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">

              <!-- User info -->
              <div class="flex items-center gap-3 mb-3 cursor-pointer" @click="selectedUserId = appeal.user?.id">
                <div class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold overflow-hidden shrink-0">
                  <img v-if="appeal.user?.avatar" :src="`http://localhost:8000/storage/${appeal.user.avatar}`" class="w-full h-full object-cover" />
                  <span v-else>{{ appeal.user?.name?.charAt(0).toUpperCase() }}</span>
                </div>
                <div>
                  <p class="text-sm font-semibold text-indigo-500 hover:text-indigo-700 transition">{{ appeal.user?.name }}</p>
                  <p class="text-xs text-gray-400">{{ formatDate(appeal.created_at) }}</p>
                </div>
              </div>

              <!-- Warning info -->
              <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3 mb-3">
                <p class="text-xs font-medium text-amber-700 dark:text-amber-400">Warning: {{ topicLabel(appeal.warning?.report_category) }}</p>
                <p class="text-xs text-amber-600 mt-0.5">{{ appeal.warning?.auto_caption }}</p>
              </div>

              <!-- Apology text -->
              <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">"{{ appeal.apology_text }}"</p>

              <!-- Proof images -->
              <div v-if="appeal.images && appeal.images.length" class="flex gap-2 mb-3 flex-wrap">
                <img v-for="img in appeal.images" :key="img.id"
                  :src="`http://localhost:8000/storage/${img.image}`"
                  class="w-20 h-20 rounded-lg object-cover cursor-pointer hover:opacity-80 transition border border-gray-200 dark:border-gray-600"
                  @click="previewImage = `http://localhost:8000/storage/${img.image}`"
                />
              </div>

              <!-- Status badge -->
              <span class="text-xs px-2.5 py-1 rounded-full font-medium" :class="{
                'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600': appeal.status === 'pending',
                'bg-green-50 dark:bg-green-900/30 text-green-600': appeal.status === 'accepted',
                'bg-red-50 dark:bg-red-900/30 text-red-600': appeal.status === 'rejected',
              }">{{ appeal.status }}</span>
            </div>

            <!-- Actions -->
            <div v-if="appeal.status === 'pending'" class="flex flex-col gap-2 shrink-0 min-w-40">
              <textarea v-model="appeal.adminResponse" placeholder="Admin response (optional)" rows="2"
                class="w-full text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2 focus:outline-none resize-none transition"></textarea>
              <button @click="reviewAppeal(appeal, 'accept')"
                class="px-4 py-2 rounded-xl bg-green-500 text-white text-xs font-medium hover:bg-green-600 transition">
                ✓ Accept (No Ban)
              </button>
              <button @click="reviewAppeal(appeal, 'reject')"
                class="px-4 py-2 rounded-xl bg-red-500 text-white text-xs font-medium hover:bg-red-600 transition">
                ✕ Reject (Add Strike)
              </button>
            </div>
            <div v-else class="text-xs text-gray-400 shrink-0">
              <p>Reviewed by {{ appeal.reviewer?.name }}</p>
              <p v-if="appeal.admin_response" class="italic mt-1">"{{ appeal.admin_response }}"</p>
            </div>
          </div>
        </div>

        <p v-if="appeals.length === 0" class="text-center text-gray-400 text-sm py-12">No appeals found</p>
      </div>

      <p v-if="msg" class="text-xs text-center" :class="msg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ msg }}</p>
    </div>

    <!-- Image preview -->
    <div v-if="previewImage" class="fixed inset-0 z-[70] bg-black/80 flex items-center justify-center" @click="previewImage = null">
      <img :src="previewImage" class="max-w-3xl max-h-[90vh] object-contain rounded-xl" />
    </div>

    <!-- User profile modal -->
    <UserProfileModal v-if="selectedUserId" :userId="selectedUserId" @close="selectedUserId = null" />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import UserProfileModal from './UserProfileModal.vue'

const appeals = ref([])
const loading = ref(true)
const activeTab = ref('pending')
const msg = ref('')
const previewImage = ref(null)
const selectedUserId = ref(null)
const search = ref('')

const tabs = [
  { label: 'Pending',  value: 'pending'  },
  { label: 'Accepted', value: 'accepted' },
  { label: 'Rejected', value: 'rejected' },
  { label: 'All',      value: 'all'      },
]

onMounted(fetchAppeals)

async function fetchAppeals() {
  loading.value = true
  try {
    const params = new URLSearchParams({ status: activeTab.value })
    if (search.value) params.set('search', search.value)
    const { data } = await axios.get(`/api/admin/appeals?${params}`)
    appeals.value = (data.data || data).map(a => ({ ...a, adminResponse: '' }))
  } catch (e) { msg.value = 'Failed to load appeals.' }
  finally { loading.value = false }
}

async function reviewAppeal(appeal, decision) {
  try {
    await axios.patch(`/api/admin/appeals/${appeal.id}/review`, {
      decision,
      admin_response: appeal.adminResponse,
    })
    msg.value = `✓ Appeal ${decision === 'accept' ? 'accepted' : 'rejected'}.`
    await fetchAppeals()
    setTimeout(() => msg.value = '', 3000)
  } catch (e) { msg.value = e?.response?.data?.message || 'Failed.' }
}

function topicLabel(topic) {
  const map = { spam: '📢 Spam', inappropriate: '🚫 Inappropriate', harassment: '😡 Harassment', copyright: '©️ Copyright', other: '❓ Other' }
  return map[topic] || topic
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>