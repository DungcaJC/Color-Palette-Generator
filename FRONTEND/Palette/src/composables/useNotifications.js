// src/composables/useNotifications.js

import { ref, computed } from 'vue'

function getKey() {
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  return user?.id ? `notifications_${user.id}` : 'notifications_guest'
}

function loadNotifications() {
  try {
    return JSON.parse(localStorage.getItem(getKey()) || '[]')
  } catch {
    return []
  }
}

const notifications = ref(loadNotifications())

function persist() {
  localStorage.setItem(getKey(), JSON.stringify(notifications.value))
}

export function useNotifications() {

  const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

  function addNotification(palette) {
    // reload in case user switched accounts
    notifications.value = loadNotifications()

    const notif = {
      id:        Date.now().toString(36) + Math.random().toString(36).slice(2),
      paletteId: palette.id,
      name:      palette.name,
      colors:    (palette.colors || []).slice(0, 5),
      date:      new Date().toISOString(),
      read:      false,
    }
    notifications.value.unshift(notif)
    if (notifications.value.length > 20) {
      notifications.value = notifications.value.slice(0, 20)
    }
    persist()
  }

  function markAllRead() {
    notifications.value = notifications.value.map(n => ({ ...n, read: true }))
    persist()
  }

  function clearNotifications() {
    notifications.value = []
    persist()
  }

  // Call this after login/logout to reload the correct user's notifications
  function reloadForUser() {
    notifications.value = loadNotifications()
  }

  return { notifications, unreadCount, addNotification, markAllRead, clearNotifications, reloadForUser }
}