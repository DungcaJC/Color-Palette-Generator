import { ref } from 'vue'
import axios from 'axios'

export function useColormind() {
  const loading = ref(false)
  const error = ref(null)

  const rgbToHex = ([r, g, b]) =>
    '#' + [r, g, b].map(v => v.toString(16).padStart(2, '0')).join('')

  const toColor = rgb => ({ rgb, hex: rgbToHex(rgb), css: `rgb(${rgb.join(',')})` })

  function sampleColorsFromImage(imgEl, count) {
    const canvas = document.createElement('canvas')
    canvas.width = 100
    canvas.height = 100
    const ctx = canvas.getContext('2d')
    ctx.drawImage(imgEl, 0, 0, 100, 100)

    const imageData = ctx.getImageData(0, 0, 100, 100).data
    const colors = []
    const zones = Math.min(count, 5)

    for (let z = 0; z < zones; z++) {
      const yStart = Math.floor((z / zones) * 100)
      const yEnd = Math.floor(((z + 1) / zones) * 100)

      let r = 0, g = 0, b = 0, total = 0

      for (let y = yStart; y < yEnd; y++) {
        for (let x = 0; x < 100; x++) {
          const idx = (y * 100 + x) * 4
          r += imageData[idx]
          g += imageData[idx + 1]
          b += imageData[idx + 2]
          total++
        }
      }

      colors.push([
        Math.round(r / total),
        Math.round(g / total),
        Math.round(b / total),
      ])
    }

    return colors
  }

  async function fetchOnce(input) {
    const { data } = await axios.post('/api/palette', { model: 'default', input })
    return data.result.map(toColor)
  }

  async function generateFromImage(imgEl, count = 5) {
    loading.value = true
    error.value = null
    try {
      const sampled = sampleColorsFromImage(imgEl, Math.min(count, 5))
      const input = [...sampled, ...Array(5 - sampled.length).fill('N')].slice(0, 5)
      const callsNeeded = count > 5 ? 2 : 1
      const results = await Promise.all(
        Array.from({ length: callsNeeded }, () => fetchOnce(input))
      )
      return results.flat().slice(0, count)
    } catch (e) {
      error.value = 'Failed to generate palette'
      return []
    } finally {
      loading.value = false
    }
  }

  return { loading, error, generateFromImage }
}