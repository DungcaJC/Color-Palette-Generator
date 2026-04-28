// src/composables/useNotifications.js
import { ref, computed } from 'vue'
import axios from 'axios'

function getKey() {
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  return user?.id ? `notifications_${user.id}` : 'notifications_guest'
}

function loadLocal() {
  try { return JSON.parse(localStorage.getItem(getKey()) || '[]') } catch { return [] }
}

const notifications = ref(loadLocal())
const serverNotifications = ref([])

function persist() {
  localStorage.setItem(getKey(), JSON.stringify(notifications.value))
}

export function useNotifications() {
  const unreadCount = computed(() =>
    notifications.value.filter(n => !n.read).length +
    serverNotifications.value.filter(n => !n.read_at).length
  )

  async function loadServerNotifications() {
    try {
      const { data } = await axios.get('/api/notifications')
      serverNotifications.value = [...data].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    } catch (e) { /* not logged in */ }
  }

  async function markServerRead(id) {
    try {
      await axios.patch(`/api/notifications/${id}/read`)
      const n = serverNotifications.value.find(n => n.id === id)
      if (n) n.read_at = new Date().toISOString()
    } catch (e) { console.error(e) }
  }

  async function markAllServerRead() {
    try {
      await axios.patch('/api/notifications/read-all')
      serverNotifications.value = serverNotifications.value.map(n => ({ ...n, read_at: new Date().toISOString() }))
    } catch (e) { console.error(e) }
  }

  function addNotification(palette) {
    notifications.value = loadLocal()
    const notif = {
      id: Date.now().toString(36) + Math.random().toString(36).slice(2),
      paletteId: palette.id,
      name: palette.name,
      colors: (palette.colors || []).slice(0, 5),
      date: new Date().toISOString(),
      read: false,
    }
    notifications.value.unshift(notif)
    if (notifications.value.length > 20) notifications.value = notifications.value.slice(0, 20)
    persist()
  }

  async function markAllRead() {
    notifications.value = notifications.value.map(n => ({ ...n, read: true }))
    persist()
    await markAllServerRead()
  }

  async function clearNotifications() {
    notifications.value = []
    persist()
    // DELETE from DB so they don't come back on reload
    try {
      await axios.delete('/api/notifications')
    } catch (e) { console.error(e) }
    serverNotifications.value = []
  }

  function reloadForUser() {
    notifications.value = loadLocal()
    loadServerNotifications()
  }

  function removeServerNotification(id) {
    serverNotifications.value = serverNotifications.value.filter(n => n.id !== id)
  }

  return {
    notifications, serverNotifications, unreadCount,
    addNotification, markAllRead, markServerRead, markAllServerRead,
    clearNotifications, reloadForUser, loadServerNotifications,
    removeServerNotification,
  }
}