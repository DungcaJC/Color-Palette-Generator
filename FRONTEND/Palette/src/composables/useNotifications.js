// src/composables/useNotifications.js

import { ref, computed } from 'vue'

const notifications = ref(JSON.parse(localStorage.getItem('notifications') || '[]'))

function persist() {
  localStorage.setItem('notifications', JSON.stringify(notifications.value))
}

export function useNotifications() {

  const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

  function addNotification(palette) {
    const notif = {
      id:        Date.now().toString(36) + Math.random().toString(36).slice(2),
      paletteId: palette.id,
      name:      palette.name,
      colors:    palette.colors.slice(0, 5),
      date:      new Date().toISOString(),
      read:      false,
    }
    notifications.value.unshift(notif)
    if (notifications.value.length > 20) notifications.value = notifications.value.slice(0, 20)
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

  return { notifications, unreadCount, addNotification, markAllRead, clearNotifications }
}