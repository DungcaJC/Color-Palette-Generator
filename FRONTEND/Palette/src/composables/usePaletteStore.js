const STORAGE_KEY = 'saved_palettes'

export function usePaletteStore() {

  function getAll() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []
    } catch {
      return []
    }
  }

  function save(palette) {
    // palette: { id, name, colors: ['#hex',...], source: 'created'|'image'|'keyword', createdAt }
    const all = getAll()
    all.unshift(palette)
    localStorage.setItem(STORAGE_KEY, JSON.stringify(all))
  }

  function remove(id) {
    const all = getAll().filter(p => p.id !== id)
    localStorage.setItem(STORAGE_KEY, JSON.stringify(all))
  }

  function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).slice(2)
  }

  return { getAll, save, remove, generateId }
}