<template>
  <!-- LearnMore.vue -->
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <!-- Hero -->
    <div class="bg-[#0d1117] pt-20 pb-28 px-8 relative overflow-hidden">
      <div class="absolute inset-0 opacity-10">
        <div class="flex flex-wrap gap-0 h-full">
          <div v-for="(c, i) in decorColors" :key="i" class="h-16 flex-1" :style="{ backgroundColor: c, minWidth: '48px' }"></div>
        </div>
      </div>
      <div class="max-w-3xl mx-auto text-center relative z-10">
        <h1 class="text-white text-5xl md:text-6xl font-black mb-5 tracking-tight leading-tight">
          Learn <span style="background: linear-gradient(to right, #6366f1, #f97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">More</span>
        </h1>
        <p class="text-gray-300 text-lg max-w-xl mx-auto leading-relaxed">
          Everything you need to know about using Palette — from generating your first colors to sharing with the community.
        </p>
      </div>
    </div>

    <!-- Quick nav -->
    <div class="sticky top-16 z-30 bg-white/80 dark:bg-gray-900/80 backdrop-blur border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-4xl mx-auto px-8 flex gap-1 overflow-x-auto py-3 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <button
          v-for="section in sections" :key="section.id"
          @click="scrollTo(section.id)"
          class="px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition shrink-0"
          :class="activeSection === section.id
            ? 'bg-indigo-600 text-white'
            : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
        >
          {{ section.label }}
        </button>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-8 py-12 flex flex-col gap-16">

      <!-- Getting started -->
      <section :id="sections[0].id" class="flex flex-col gap-6">
        <div>
          <p class="text-xs text-indigo-500 uppercase tracking-widest font-medium mb-2">01</p>
          <h2 class="text-3xl font-black text-gray-800 dark:text-white">Getting Started</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-4">
          <div v-for="(step, i) in gettingStarted" :key="i"
            class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 relative overflow-hidden">
            <span class="absolute top-4 right-4 text-4xl font-black text-gray-100 dark:text-gray-700">{{ i + 1 }}</span>
            <div class="text-2xl mb-3">{{ step.emoji }}</div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-1.5">{{ step.title }}</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">{{ step.desc }}</p>
          </div>
        </div>
      </section>

      <!-- Tools guide -->
      <section :id="sections[1].id" class="flex flex-col gap-6">
        <div>
          <p class="text-xs text-indigo-500 uppercase tracking-widest font-medium mb-2">02</p>
          <h2 class="text-3xl font-black text-gray-800 dark:text-white">The Tools</h2>
        </div>
        <div class="flex flex-col gap-4">
          <div v-for="tool in tools" :key="tool.title"
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center gap-4 p-5 border-b border-gray-50 dark:border-gray-700 cursor-pointer" @click="tool.open = !tool.open">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shrink-0" :style="{ background: tool.color + '20' }">{{ tool.emoji }}</div>
              <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ tool.title }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ tool.subtitle }}</p>
              </div>
              <svg class="w-4 h-4 text-gray-400 transition-transform shrink-0" :class="tool.open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
            </div>
            <div v-if="tool.open" class="px-5 pb-5 pt-4">
              <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-4">{{ tool.desc }}</p>
              <div class="flex flex-col gap-2">
                <div v-for="step in tool.steps" :key="step" class="flex gap-3 items-start">
                  <span class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-xs flex items-center justify-center shrink-0 mt-0.5 font-medium">✓</span>
                  <p class="text-sm text-gray-600 dark:text-gray-300">{{ step }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Community guide -->
      <section :id="sections[2].id" class="flex flex-col gap-6">
        <div>
          <p class="text-xs text-indigo-500 uppercase tracking-widest font-medium mb-2">03</p>
          <h2 class="text-3xl font-black text-gray-800 dark:text-white">Community</h2>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700">
          <div v-for="tip in communityTips" :key="tip.title" class="p-5 flex gap-4 items-start">
            <span class="text-2xl shrink-0">{{ tip.emoji }}</span>
            <div>
              <p class="text-sm font-semibold text-gray-800 dark:text-white mb-1">{{ tip.title }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ tip.desc }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Color theory -->
      <section :id="sections[3].id" class="flex flex-col gap-6">
        <div>
          <p class="text-xs text-indigo-500 uppercase tracking-widest font-medium mb-2">04</p>
          <h2 class="text-3xl font-black text-gray-800 dark:text-white">Color Theory Basics</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
          <div v-for="theory in colorTheory" :key="theory.title"
            class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700">
            <!-- Color example -->
            <div class="flex gap-1.5 mb-4 h-10 rounded-xl overflow-hidden">
              <div v-for="c in theory.example" :key="c" class="flex-1" :style="{ backgroundColor: c }"></div>
            </div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-1.5">{{ theory.title }}</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">{{ theory.desc }}</p>
          </div>
        </div>
      </section>

      <!-- FAQ -->
      <section :id="sections[4].id" class="flex flex-col gap-6">
        <div>
          <p class="text-xs text-indigo-500 uppercase tracking-widest font-medium mb-2">05</p>
          <h2 class="text-3xl font-black text-gray-800 dark:text-white">FAQ</h2>
        </div>
        <div class="flex flex-col gap-3">
          <div v-for="faq in faqs" :key="faq.q"
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <button class="w-full text-left p-5 flex items-center justify-between gap-4" @click="faq.open = !faq.open">
              <p class="text-sm font-medium text-gray-800 dark:text-white">{{ faq.q }}</p>
              <svg class="w-4 h-4 text-gray-400 transition-transform shrink-0" :class="faq.open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <div v-if="faq.open" class="px-5 pb-5">
              <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ faq.a }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <div class="rounded-2xl p-8 text-center" style="background: linear-gradient(135deg, #0f172a, #1e1b4b)">
        <h2 class="text-white text-2xl font-black mb-2">Start creating today</h2>
        <p class="text-gray-400 text-sm mb-5">Your perfect palette is just a few clicks away.</p>
        <button @click="$emit('navigate', 'KeywordColorPalette')"
          class="px-6 py-3 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
          style="background: linear-gradient(to right, #4f46e5, #f97316)">
          Generate Your First Palette →
        </button>
      </div>
    </div>

    <Footer @navigate="$emit('navigate', $event)" />
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import Footer from './Footer.vue'

defineEmits(['navigate'])

const decorColors = ['#6366f1','#8b5cf6','#a855f7','#ec4899','#f43f5e','#f97316','#eab308','#84cc16','#22c55e','#06b6d4','#3b82f6','#6366f1']

const sections = [
  { id: 'getting-started', label: '🚀 Getting Started' },
  { id: 'tools',           label: '🛠 Tools'           },
  { id: 'community',       label: '🌍 Community'       },
  { id: 'color-theory',    label: '🎨 Color Theory'    },
  { id: 'faq',             label: '❓ FAQ'             },
]

const activeSection = ref('getting-started')

function scrollTo(id) {
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

const gettingStarted = [
  { emoji: '👤', title: 'Create an account', desc: 'Sign up for free to save palettes and join the community. No credit card needed.' },
  { emoji: '🎨', title: 'Generate a palette', desc: 'Pick a tool — keyword, image, or manual — and create your first color palette in seconds.' },
  { emoji: '💾', title: 'Save and share', desc: 'Save your favorite palettes, post your artwork, and explore what others are creating.' },
]

const tools = ref([
  {
    emoji: '🔍', title: 'Keyword Palette', subtitle: 'Generate colors from words',
    color: '#6366f1', open: false,
    desc: 'Type any word, emotion, or theme and instantly get a color palette that matches the vibe. Great for branding, design projects, or just exploring.',
    steps: ['Go to Keyword Palette from the navbar', 'Type a word like "sunset", "ocean", or "retro"', 'Browse through palette suggestions', 'Select individual colors or the whole palette', 'Click Save to keep your favorites'],
  },
  {
    emoji: '🖼', title: 'Generate from Image', subtitle: 'Extract colors from photos',
    color: '#ec4899', open: false,
    desc: 'Upload any image — a photo, artwork, screenshot — and we\'ll extract the most dominant colors to build a palette from it.',
    steps: ['Go to Generate Image from the navbar', 'Upload an image file (JPG, PNG, WebP)', 'We automatically extract dominant colors using k-means', 'Select the palette variations you like', 'Save the palettes you want to keep'],
  },
  {
    emoji: '✏️', title: 'Create Palette', subtitle: 'Manual color builder',
    color: '#f97316', open: false,
    desc: 'Have exact colors in mind? Use our manual builder to enter HEX codes, pick from a color wheel, and build your palette color by color.',
    steps: ['Go to Create Palette from the navbar', 'Click the + button to add a new color', 'Use the color picker or enter a HEX value', 'Add as many colors as you need', 'Name and save your custom palette'],
  },
])

const communityTips = [
  { emoji: '📸', title: 'Posting your art',     desc: 'When creating a post, you can attach a palette you used. This helps others see the colors behind your artwork.' },
  { emoji: '❤️', title: 'Liking and saving',    desc: 'Like posts to show appreciation and save them to your Saved Posts collection for later reference.' },
  { emoji: '💬', title: 'Comments and replies', desc: 'Leave comments on posts and reply to others. Engage with the creative community.' },
  { emoji: '🚩', title: 'Reporting content',    desc: 'If you see something that violates community guidelines, use the Report button. Our moderators review all reports.' },
  { emoji: '🔍', title: 'Searching',            desc: 'Use the search bar to find posts by caption, artist name, or browse by category and post type.' },
]

const colorTheory = [
  { title: 'Complementary',  desc: 'Colors opposite each other on the wheel. High contrast, vibrant look. Great for making things pop.',                        example: ['#6366f1','#f1c40f'] },
  { title: 'Analogous',      desc: 'Colors next to each other on the wheel. Harmonious and pleasing. Common in nature and relaxing designs.',                  example: ['#6366f1','#8b5cf6','#a855f7','#c084fc'] },
  { title: 'Triadic',        desc: 'Three colors equally spaced around the wheel. Vibrant and balanced. Works well when one color dominates.',                  example: ['#6366f1','#f97316','#22c55e'] },
  { title: 'Monochromatic',  desc: 'Different shades of the same hue. Elegant and cohesive. Easy to implement and always looks professional.',                 example: ['#1e1b4b','#3730a3','#6366f1','#a5b4fc','#e0e7ff'] },
  { title: 'Split-Complementary', desc: 'Base color plus two colors adjacent to its complement. Less tension than complementary but still high contrast.',      example: ['#6366f1','#f97316','#22c55e'] },
  { title: 'Tetradic',       desc: 'Four colors forming two complementary pairs. Rich and complex. Works best when one color leads.',                           example: ['#6366f1','#f97316','#ec4899','#22c55e'] },
]

const faqs = ref([
  { q: 'Is Palette free to use?',                          a: 'Yes! All core features are completely free. Create an account to unlock saving and community features.',                                   open: false },
  { q: 'Can I use the palettes in my projects?',           a: 'Absolutely. Colors themselves cannot be copyrighted. Use any palette you generate or save in any personal or commercial project.',        open: false },
  { q: 'How does image color extraction work?',            a: 'We use a k-means clustering algorithm that groups similar pixels together and picks the center color of each cluster as the dominant color.',open: false },
  { q: 'Can I export my palettes?',                        a: 'Yes! Go to Settings → Export All Palettes to download all your saved palettes as a JSON file.',                                           open: false },
  { q: 'What happens if I get a warning?',                 a: 'You\'ll receive a notification explaining the reason. You can submit an appeal with proof. Unresolved warnings may result in a temporary ban.',open: false },
  { q: 'How do I delete my account?',                      a: 'Go to Settings → Danger Zone → Delete Account. This permanently removes all your data. This action cannot be undone.',                    open: false },
])

// Active section tracking
let observer
onMounted(() => {
  observer = new IntersectionObserver(
    entries => {
      entries.forEach(e => { if (e.isIntersecting) activeSection.value = e.target.id })
    },
    { threshold: 0.3, rootMargin: '-80px 0px 0px 0px' }
  )
  sections.forEach(s => {
    const el = document.getElementById(s.id)
    if (el) observer.observe(el)
  })
})

onBeforeUnmount(() => observer?.disconnect())
</script>