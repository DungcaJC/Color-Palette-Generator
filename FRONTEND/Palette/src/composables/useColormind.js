import { ref } from 'vue'
import axios from 'axios'

export function useColormind() {
  const loading = ref(false)
  const error = ref(null)

  const rgbToHex = ([r, g, b]) =>
    '#' + [r, g, b].map(v => v.toString(16).padStart(2, '0')).join('')

  const toColor = rgb => ({ rgb, hex: rgbToHex(rgb), css: `rgb(${rgb.join(',')})` })

  // Extract a large pool of dominant colors using k-means
  function extractColorPool(imgEl, poolSize = 30) {
    const canvas = document.createElement('canvas')
    const size = 150
    canvas.width = size
    canvas.height = size
    const ctx = canvas.getContext('2d')
    ctx.drawImage(imgEl, 0, 0, size, size)

    const imageData = ctx.getImageData(0, 0, size, size).data
    const pixels = []

    for (let i = 0; i < imageData.length; i += 4 * 2) {
      const r = imageData[i]
      const g = imageData[i + 1]
      const b = imageData[i + 2]
      const a = imageData[i + 3]
      if (a < 128) continue
      pixels.push([r, g, b])
    }

    const k = poolSize
    let centroids = []
    const step = Math.floor(pixels.length / k)
    for (let i = 0; i < k; i++) {
      centroids.push([...pixels[Math.min(i * step, pixels.length - 1)]])
    }

    // K-means 15 iterations
    for (let iter = 0; iter < 15; iter++) {
      const clusters = Array.from({ length: k }, () => [])

      for (const pixel of pixels) {
        let minDist = Infinity
        let closest = 0
        for (let ci = 0; ci < k; ci++) {
          const dr = pixel[0] - centroids[ci][0]
          const dg = pixel[1] - centroids[ci][1]
          const db = pixel[2] - centroids[ci][2]
          const dist = dr * dr + dg * dg + db * db
          if (dist < minDist) { minDist = dist; closest = ci }
        }
        clusters[closest].push(pixel)
      }

      for (let ci = 0; ci < k; ci++) {
        if (clusters[ci].length === 0) continue
        const sum = clusters[ci].reduce(
          (acc, p) => [acc[0] + p[0], acc[1] + p[1], acc[2] + p[2]],
          [0, 0, 0]
        )
        centroids[ci] = sum.map(v => Math.round(v / clusters[ci].length))
      }
    }

    // Final pass — count cluster sizes for sorting by dominance
    const clusterData = centroids.map(c => ({ centroid: c, count: 0 }))

    for (const pixel of pixels) {
      let minDist = Infinity
      let closest = 0
      for (let ci = 0; ci < k; ci++) {
        const dr = pixel[0] - centroids[ci][0]
        const dg = pixel[1] - centroids[ci][1]
        const db = pixel[2] - centroids[ci][2]
        const dist = dr * dr + dg * dg + db * db
        if (dist < minDist) { minDist = dist; closest = ci }
      }
      clusterData[closest].count++
    }

    // Sort by dominance — most common first
    return clusterData
      .filter(c => c.count > 0)
      .sort((a, b) => b.count - a.count)
      .map(c => c.centroid)
  }

  async function generateFromImage(imgEl, count = 5, paletteIndex = 0) {
    loading.value = true
    error.value = null
    try {
      // Extract a large pool once
      const pool = extractColorPool(imgEl, Math.max(30, count * 7))

      // Each palette takes a different slice from the pool
      // Palette 0 → most dominant colors
      // Palette 1 → next set of colors
      // Palette 2 → next set, etc.
      const start = paletteIndex * count
      const slice = pool.slice(start, start + count)

      // If we run out of pool colors, cycle back with slight offset
      const result = []
      for (let i = 0; i < count; i++) {
        result.push(pool[(start + i) % pool.length])
      }

      return result.map(toColor)
    } catch (e) {
      console.error('Extraction error:', e)
      error.value = e?.message || 'Failed to extract colors'
      return []
    } finally {
      loading.value = false
    }
  }

  async function generateFromSeeds(seeds, count = 5) {
    loading.value = true
    error.value = null
    try {
      const input = [...seeds.slice(0, 5), ...Array(Math.max(0, 5 - seeds.length)).fill('N')]
      const { data } = await axios.post('/api/palette', { model: 'default', input })
      return data.result.map(toColor).slice(0, count)
    } catch (e) {
      error.value = 'Failed to generate palette'
      return []
    } finally {
      loading.value = false
    }
  }

  return { loading, error, generateFromImage, generateFromSeeds }
}