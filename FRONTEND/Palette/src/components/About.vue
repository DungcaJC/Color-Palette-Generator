<template>
  <!-- About.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <!-- Hero -->
    <div class="bg-[#0d1117] pt-20 pb-28 px-8 relative overflow-hidden">
      <!-- Decorative color blobs -->
      <div class="absolute top-10 right-20 w-64 h-64 rounded-full opacity-20 blur-3xl" style="background: radial-gradient(circle, #6366f1, #ec4899)"></div>
      <div class="absolute bottom-0 left-10 w-48 h-48 rounded-full opacity-15 blur-3xl" style="background: radial-gradient(circle, #f97316, #eab308)"></div>

      <div class="max-w-3xl mx-auto text-center relative z-10">
        <div class="flex justify-center gap-2 mb-6">
          <div v-for="c in palette" :key="c" class="w-5 h-5 rounded-full animate-fade-in-up" :style="{ backgroundColor: c }"></div>
        </div>
        <h1 class="text-white text-5xl md:text-6xl font-black mb-5 leading-tight tracking-tight">
          About<br><span style="background: linear-gradient(to right, #6366f1, #f97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Palette</span>
        </h1>
        <p class="text-gray-300 text-lg leading-relaxed max-w-xl mx-auto">
          A creative tool built for designers, artists, and anyone who thinks in color.
        </p>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-4xl mx-auto px-8 py-16 flex flex-col gap-16">

      <!-- Story -->
      <div class="animate-fade-in-up">
        <p class="text-xs text-indigo-500 uppercase tracking-widest font-medium mb-3">The Story</p>
        <h2 class="text-3xl font-black text-gray-800 dark:text-white mb-5">Why we built this</h2>
        <p class="text-gray-500 dark:text-gray-400 text-lg leading-relaxed mb-4">
          Palette started as a simple idea — what if finding the perfect colors was as easy as typing a word? Too many designers spend hours browsing color sites, only to end up with palettes that don't feel right.
        </p>
        <p class="text-gray-500 dark:text-gray-400 text-lg leading-relaxed">
          So we built three ways to generate palettes: from keywords, from images, or from scratch. Then we realized creators want to share what they make — so we added a whole community around it.
        </p>
      </div>

      <!-- What we offer -->
      <div class="animate-fade-in-up-delay-1">
        <p class="text-xs text-indigo-500 uppercase tracking-widest font-medium mb-3">What We Offer</p>
        <h2 class="text-3xl font-black text-gray-800 dark:text-white mb-8">Everything about color</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div v-for="feature in features" :key="feature.title"
            class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition group">
            <div class="text-3xl mb-3">{{ feature.emoji }}</div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white mb-2">{{ feature.title }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ feature.desc }}</p>
          </div>
        </div>
      </div>

      <!-- Values -->
      <div class="animate-fade-in-up-delay-2">
        <p class="text-xs text-indigo-500 uppercase tracking-widest font-medium mb-3">Our Values</p>
        <h2 class="text-3xl font-black text-gray-800 dark:text-white mb-8">What we believe in</h2>
        <div class="flex flex-col gap-4">
          <div v-for="(val, i) in values" :key="val.label" class="flex gap-5 items-start p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0" :style="{ backgroundColor: val.color + '20', color: val.color }">{{ val.icon }}</div>
            <div>
              <p class="text-sm font-semibold text-gray-800 dark:text-white mb-1">{{ val.label }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ val.desc }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <div class="rounded-2xl overflow-hidden relative" style="background: linear-gradient(135deg, #1e1b4b, #312e81, #4c1d95)">
        <div class="absolute top-0 right-0 w-48 h-48 opacity-20 blur-2xl rounded-full" style="background: #f97316; transform: translate(30%, -30%)"></div>
        <div class="relative z-10 p-10 text-center">
          <h2 class="text-white text-3xl font-black mb-3">Ready to start?</h2>
          <p class="text-indigo-200 mb-6">Generate your first palette in seconds. No account needed.</p>
          <div class="flex gap-3 justify-center flex-wrap">
            <button @click="$emit('navigate', 'KeywordColorPalette')"
              class="px-6 py-3 rounded-xl bg-white text-indigo-700 text-sm font-semibold hover:bg-indigo-50 transition">
              Try Keyword Palette
            </button>
            <button @click="$emit('navigate', 'Community')"
              class="px-6 py-3 rounded-xl border border-white/30 text-white text-sm font-semibold hover:bg-white/10 transition">
              Explore Community
            </button>
          </div>
        </div>
      </div>

    </div>

    <Footer @navigate="$emit('navigate', $event)" />
  </div>
</template>

<script setup>
import Footer from './Footer.vue'

defineEmits(['navigate'])

const palette = ['#6366f1','#8b5cf6','#ec4899','#f97316','#eab308','#22c55e','#06b6d4']

const features = [
  { emoji: '🔍', title: 'Keyword Generation',    desc: 'Type any word or mood and get an instant palette that matches the vibe. Perfect for quick inspiration.' },
  { emoji: '🖼',  title: 'Image Extraction',     desc: 'Upload any photo and we extract the dominant colors to build a palette from real-world visuals.' },
  { emoji: '🎨', title: 'Manual Creation',       desc: 'Full control with HEX, RGB, and HSL inputs. Build exactly the palette you have in mind.' },
  { emoji: '💾', title: 'Save & Organize',       desc: 'Keep your favorite palettes saved and organized. Access them anytime from any device.' },
  { emoji: '🌍', title: 'Community Sharing',     desc: 'Share your creations with the world. Like, comment, and save palettes from other users.' },
  { emoji: '🎭', title: 'Creative Posts',        desc: 'Post your artwork alongside the palette you used. Inspire others and get inspired.' },
]

const values = [
  { icon: '🎯', label: 'Simplicity first',     desc: 'Color tools should be fast and intuitive. No complicated menus or learning curves.',           color: '#6366f1' },
  { icon: '🌈', label: 'Color for everyone',   desc: 'Whether you\'re a professional designer or just exploring, Palette works for you.',              color: '#ec4899' },
  { icon: '🤝', label: 'Community-driven',     desc: 'The best ideas come from sharing. Our community features are built around creative exchange.',   color: '#f97316' },
  { icon: '🔒', label: 'Respect & Safety',     desc: 'We take community safety seriously with a proper reporting, warning, and appeal system.',        color: '#22c55e' },
]
</script>