// useNotifications.js

import { ref } from 'vue'

const NOTIF_KEY = 'palette_notifications'

const notifications = ref(loadNotifs())

function loadNotifs() {
  return JSON.parse(localStorage.getItem(NOTIF_KEY) || '[]')
}

function saveNotifs() {
  localStorage.setItem(NOTIF_KEY, JSON.stringify(notifications.value))
}

export function useNotifications() {

  function addNotification(palette) {
    notifications.value.unshift({
      id:        palette.id,
      paletteId: palette.id,
      name:      palette.name,
      colors:    palette.colors.slice(0, 5),
      read:      false,
      date:      new Date().toISOString(),
    })
    saveNotifs()
  }

  function markAllRead() {
    notifications.value.forEach(n => n.read = true)
    saveNotifs()
  }

  function markRead(id) {
    const n = notifications.value.find(n => n.id === id)
    if (n) n.read = false
    saveNotifs()
  }

  function clearNotifications() {
    notifications.value = []
    saveNotifs()
  }

  const unreadCount = ref(0)
  function syncUnread() {
    unreadCount.value = notifications.value.filter(n => !n.read).length
  }
  syncUnread()

  return { notifications, unreadCount, addNotification, markAllRead, markRead, clearNotifications, syncUnread }
}