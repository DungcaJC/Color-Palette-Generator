// usePaletteStore.js

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
    const { data } = await axios.get('/api/palettes')
    return data
  }

  async function save(palette) {
    if (!isLoggedIn()) {
      const existing = JSON.parse(localStorage.getItem(GUEST_KEY) || '[]')
      existing.push(palette)
      localStorage.setItem(GUEST_KEY, JSON.stringify(existing))
      return
    }
    const { data } = await axios.post('/api/palettes', {
      name:   palette.name,
      colors: palette.colors,
      source: palette.source,
    })
    return data
  }

  async function remove(id) {
    if (!isLoggedIn()) {
      const existing = JSON.parse(localStorage.getItem(GUEST_KEY) || '[]')
      const updated = existing.filter(p => p.id !== id)
      localStorage.setItem(GUEST_KEY, JSON.stringify(updated))
      return
    }
    await axios.delete(`/api/palettes/${id}`)
  }

  function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).slice(2)
  }

  return { getAll, save, remove, generateId }
}