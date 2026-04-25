<template>
  <!-- AdminReports.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <div class="bg-[#0d1117] pt-10 pb-20 px-8">
      <div class="max-w-6xl mx-auto">
        <div class="flex items-center gap-3 mb-1"><span class="text-2xl">🚨</span><h1 class="text-white text-2xl font-semibold">Reports</h1></div>
        <p class="text-gray-400 text-sm ml-9">Review reported posts from the community</p>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-8 -mt-12 pb-16 flex flex-col gap-4">

      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex gap-2">
        <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value; fetchReports()"
          class="px-4 py-2 rounded-full text-sm font-medium border transition"
          :class="activeTab === tab.value ? 'bg-black dark:bg-white text-white dark:text-black border-black dark:border-white' : 'bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:border-gray-400'"
        >{{ tab.label }}</button>
      </div>

      <div v-if="loading" class="flex justify-center py-24">
        <div class="w-6 h-6 border-2 border-gray-300 border-t-red-500 rounded-full animate-spin"></div>
      </div>

      <div v-else class="flex flex-col gap-4">
        <div v-for="report in reports" :key="report.id" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="flex gap-4 p-5">

            <!-- Post preview — clickable -->
            <div @click="openPostModal(report)" class="w-24 h-24 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0 cursor-pointer hover:opacity-80 transition">
              <img v-if="report.post?.image" :src="`http://localhost:8000/storage/${report.post.image}`" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-3xl">🎨</div>
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="topicColor(report.topic)">{{ topicLabel(report.topic) }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="{ 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600': report.status === 'pending', 'bg-green-50 dark:bg-green-900/30 text-green-600': report.status === 'reviewed', 'bg-gray-50 dark:bg-gray-700 text-gray-500': report.status === 'dismissed' }">{{ report.status }}</span>
                  </div>
                  <!-- Clickable reporter name -->
                  <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
                    <span class="font-medium text-indigo-500 cursor-pointer hover:text-indigo-700" @click="openUserModal(report.reporter)">{{ report.reporter?.name }}</span>
                    reported a post by
                    <span class="font-medium text-indigo-500 cursor-pointer hover:text-indigo-700" @click="openUserModal(report.post?.user)">{{ report.post?.user?.name }}</span>
                  </p>
                  <p v-if="report.details" class="text-xs text-gray-400 italic">"{{ report.details }}"</p>
                  <p class="text-xs text-gray-400 mt-1">{{ formatDate(report.created_at) }}</p>
                </div>

                <div class="flex flex-col gap-2 shrink-0">
                  <select v-model="report.status" @change="updateStatus(report)"
                    class="text-xs border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-1.5 focus:outline-none transition">
                    <option value="pending">Pending</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="dismissed">Dismissed</option>
                  </select>
                  <button @click="openWarningModal(report)" class="text-xs text-amber-500 hover:text-amber-700 border border-amber-200 hover:border-amber-400 px-3 py-1.5 rounded-lg transition">⚠️ Warning</button>
                  <button @click="deleteReportedPost(report)" class="text-xs text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-lg transition">Delete Post</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <p v-if="reports.length === 0" class="text-center text-gray-400 text-sm py-12">No reports found</p>
      </div>

      <p v-if="msg" class="text-xs text-center" :class="msg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ msg }}</p>
    </div>

    <!-- Post View Modal -->
    <div v-if="activePost" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center px-4" @click.self="activePost = null">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex overflow-hidden [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <div class="w-1/2 bg-black flex items-center justify-center shrink-0">
          <img v-if="activePost.image" :src="`http://localhost:8000/storage/${activePost.image}`" class="w-full h-full object-contain max-h-[90vh]" />
          <div v-else class="text-6xl">🎨</div>
        </div>
        <div class="flex-1 flex flex-col overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold overflow-hidden">
                <img v-if="activePost.user?.avatar" :src="`http://localhost:8000/storage/${activePost.user.avatar}`" class="w-full h-full object-cover" />
                <span v-else>{{ activePost.user?.name?.charAt(0).toUpperCase() }}</span>
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ activePost.user?.name }}</p>
                <p class="text-xs text-gray-400">{{ activePost.category }}</p>
              </div>
            </div>
            <button @click="activePost = null" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
          </div>
          <div class="px-5 py-4 flex-1">
            <p v-if="activePost.caption" class="text-sm text-gray-600 dark:text-gray-300">{{ activePost.caption }}</p>
          </div>
          <div v-if="activePost.colors && activePost.colors.length" class="px-5 pb-4">
            <div class="flex gap-1 h-8 rounded-lg overflow-hidden">
              <div v-for="(c, i) in activePost.colors" :key="i" class="flex-1" :style="{ backgroundColor: c }"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- User Profile Modal -->
    <div v-if="selectedUser" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center px-4" @click.self="selectedUser = null">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md max-h-[80vh] overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <div class="bg-[#0d1117] rounded-t-2xl p-6 flex items-center gap-4 relative">
          <button @click="selectedUser = null" class="absolute top-4 right-4 text-gray-400 hover:text-white">✕</button>
          <div class="w-14 h-14 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xl font-bold overflow-hidden shrink-0">
            <img v-if="selectedUser.avatar" :src="`http://localhost:8000/storage/${selectedUser.avatar}`" class="w-full h-full object-cover" />
            <span v-else>{{ selectedUser.name?.charAt(0).toUpperCase() }}</span>
          </div>
          <div>
            <h2 class="text-white font-bold">{{ selectedUser.name }}</h2>
            <p class="text-gray-400 text-sm">{{ selectedUser.email }}</p>
          </div>
        </div>
        <div class="p-5">
          <div class="grid grid-cols-3 gap-2">
            <div v-for="post in selectedUserPosts" :key="post.id" class="aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
              <img v-if="post.image" :src="`http://localhost:8000/storage/${post.image}`" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-2xl">🎨</div>
            </div>
          </div>
          <p v-if="!selectedUserPosts.length" class="text-center text-gray-400 text-sm py-4">No posts</p>
        </div>
      </div>
    </div>

    <!-- Warning Modal -->
    <div v-if="warningTarget" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center px-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 flex flex-col gap-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-800 dark:text-white">⚠️ Send Warning</h2>
          <button @click="warningTarget = null" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3">
          <p class="text-xs text-amber-700 dark:text-amber-400 font-medium">Sending warning to: <span class="font-bold">{{ warningTarget.post?.user?.name }}</span></p>
          <p class="text-xs text-amber-600 mt-1">Report: {{ topicLabel(warningTarget.topic) }}</p>
        </div>

        <!-- Auto caption preview -->
        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Auto warning message</p>
          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3 text-sm text-gray-600 dark:text-gray-300">{{ autoCaptions[warningTarget.topic] }}</div>
        </div>

        <!-- Expiry days -->
        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Days before ban if unresolved</p>
          <div class="flex gap-2">
            <button v-for="d in [1,3,5]" :key="d" @click="warningForm.expires_days = d"
              class="flex-1 py-2 rounded-xl border text-sm font-medium transition"
              :class="warningForm.expires_days === d ? 'bg-amber-500 text-white border-amber-500' : 'bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-600 hover:border-amber-300'"
            >{{ d }} day{{ d > 1 ? 's' : '' }}</button>
          </div>
        </div>

        <!-- Admin additional text -->
        <div>
          <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Additional message (optional)</p>
          <textarea v-model="warningForm.admin_text" placeholder="Add a personal note to this warning..." rows="2"
            class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none resize-none transition"></textarea>
        </div>

        <p v-if="warningMsg" class="text-xs" :class="warningMsg.includes('✓') ? 'text-green-500' : 'text-red-500'">{{ warningMsg }}</p>

        <div class="flex gap-3">
          <button @click="warningTarget = null" class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-500 transition">Cancel</button>
          <button @click="sendWarning" :disabled="sendingWarning"
            class="flex-1 py-2.5 rounded-xl bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 disabled:opacity-40 transition">
            {{ sendingWarning ? 'Sending...' : '⚠️ Send Warning' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const reports = ref([])
const loading = ref(true)
const activeTab = ref('pending')
const msg = ref('')
const activePost = ref(null)
const selectedUser = ref(null)
const selectedUserPosts = ref([])
const warningTarget = ref(null)
const warningForm = ref({ expires_days: 1, admin_text: '' })
const warningMsg = ref('')
const sendingWarning = ref(false)

const tabs = [
  { label: 'Pending',   value: 'pending'   },
  { label: 'Reviewed',  value: 'reviewed'  },
  { label: 'Dismissed', value: 'dismissed' },
  { label: 'All',       value: 'all'       },
]

const autoCaptions = {
  spam:          'Your post has been flagged for spam. Repeated violations will result in a ban.',
  inappropriate: 'Your post contains inappropriate content that violates our community guidelines.',
  harassment:    'Your post has been flagged for harassment. This behavior is not tolerated.',
  copyright:     'Your post may contain copyrighted content without proper attribution.',
  other:         'Your post has been flagged for violating our community guidelines.',
}

onMounted(fetchReports)

async function fetchReports() {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/admin/reports?status=${activeTab.value}`)
    reports.value = data.data || data
  } catch (e) { msg.value = 'Failed to load reports.' }
  finally { loading.value = false }
}

function openPostModal(report) {
  if (report.post) activePost.value = report.post
}

async function openUserModal(user) {
  if (!user) return
  selectedUser.value = user
  try {
    const { data } = await axios.get(`/api/users/${user.id}/profile`)
    selectedUserPosts.value = data.posts || []
  } catch (e) { selectedUserPosts.value = [] }
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
    warningMsg.value = e?.response?.data?.message || 'Failed to send warning.'
  } finally {
    sendingWarning.value = false
  }
}

async function updateStatus(report) {
  try {
    await axios.patch(`/api/admin/reports/${report.id}/status`, { status: report.status })
    msg.value = '✓ Status updated.'
    setTimeout(() => msg.value = '', 3000)
  } catch (e) { msg.value = 'Failed to update.' }
}

async function deleteReportedPost(report) {
  try {
    await axios.delete(`/api/admin/posts/${report.post_id}`)
    reports.value = reports.value.filter(r => r.post_id !== report.post_id)
    msg.value = '✓ Post deleted.'
    setTimeout(() => msg.value = '', 3000)
  } catch (e) { msg.value = 'Failed.' }
}

function topicLabel(topic) {
  const map = { spam: '📢 Spam', inappropriate: '🚫 Inappropriate', harassment: '😡 Harassment', copyright: '©️ Copyright', other: '❓ Other' }
  return map[topic] || topic
}

function topicColor(topic) {
  const map = { spam: 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600', inappropriate: 'bg-red-50 dark:bg-red-900/30 text-red-600', harassment: 'bg-orange-50 dark:bg-orange-900/30 text-orange-600', copyright: 'bg-purple-50 dark:bg-purple-900/30 text-purple-600', other: 'bg-gray-50 dark:bg-gray-700 text-gray-500' }
  return map[topic] || ''
}

function formatDate(iso) {
  if (!iso) return '-'
  return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>