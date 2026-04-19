// src/composables/usePaletteStore.js

import axios from 'axios'
import { useAuth } from './useAuth'

const GUEST_KEY = 'guest_saved_palettes'

export function usePaletteStore() {
  const { isLoggedIn } = useAuth()

  async function getAll() {
    if (!isLoggedIn()) {
      const stored = localStorage.getItem(GUEST_KEY)
      return stored ? JSON.parse(stored) : []
    }
    try {
      const { data } = await axios.get('/api/palettes')
      return data
    } catch (e) {
      console.error('Failed to fetch palettes:', e)
      return []
    }
  }

  async function save(palette) {
    if (!isLoggedIn()) {
      const existing = JSON.parse(localStorage.getItem(GUEST_KEY) || '[]')
      existing.unshift(palette)
      localStorage.setItem(GUEST_KEY, JSON.stringify(existing))
      return palette
    }
    try {
      const { data } = await axios.post('/api/palettes', {
        name:   palette.name,
        colors: palette.colors,
        source: palette.source,
      })
      return data
    } catch (e) {
      console.error('Failed to save palette:', e?.response?.data || e)
      throw e
    }
  }

  async function remove(id) {
    if (!isLoggedIn()) {
      const existing = JSON.parse(localStorage.getItem(GUEST_KEY) || '[]')
      localStorage.setItem(GUEST_KEY, JSON.stringify(existing.filter(p => p.id !== id)))
      return
    }
    await axios.delete(`/api/palettes/${id}`)
  }

  async function clearAll() {
    if (!isLoggedIn()) {
      localStorage.removeItem(GUEST_KEY)
      return
    }
    await axios.delete('/api/palettes/all')
  }

  function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).slice(2)
  }

  return { getAll, save, remove, clearAll, generateId }
}