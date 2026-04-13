import { ref } from 'vue'
import axios from 'axios'

export function useColorMagic() {
  const palettes = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function searchPalettes(query = 'random') {
    loading.value = true
    error.value = null
    try {
      const { data } = await axios.get(`/api/palette/search?q=${encodeURIComponent(query)}`)
      palettes.value = data
    } catch (e) {
      error.value = 'Failed to fetch palettes'
    } finally {
      loading.value = false
    }
  }

  return { palettes, loading, error, searchPalettes }
}