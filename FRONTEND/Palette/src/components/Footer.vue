<template>
  <!-- Footer.vue -->
  <footer class="bg-[#0d1117] border-t border-white/10 mt-auto">
    <div class="max-w-7xl mx-auto px-8 py-12">

      <!-- Top row -->
      <div class="flex flex-col md:flex-row items-start justify-between gap-10 mb-12">

        <!-- Brand -->
        <div class="flex-1 max-w-xs">
          <div class="flex items-center gap-3 mb-4 cursor-pointer" @click="$emit('navigate', 'Heroes')">
            <img :src="logo" class="h-8 w-auto" alt="Palette" />
            <span class="text-white text-lg font-bold">Palette</span>
          </div>
          <p class="text-gray-400 text-sm leading-relaxed">
            A creative space to generate, explore, and share color palettes. From image extraction to keyword-based generation — find your colors here.
          </p>
          <!-- Social links -->
          <div class="flex gap-3 mt-5">
            <a href="https://github.com" target="_blank" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/10 flex items-center justify-center text-gray-400 hover:text-white transition">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
              </svg>
            </a>
            <a href="https://twitter.com" target="_blank" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/10 flex items-center justify-center text-gray-400 hover:text-white transition">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
              </svg>
            </a>
            <a href="https://instagram.com" target="_blank" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/10 flex items-center justify-center text-gray-400 hover:text-white transition">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
              </svg>
            </a>
          </div>
        </div>

        <!-- Links -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-8 flex-1 max-w-lg">

          <div>
            <p class="text-xs text-gray-500 uppercase tracking-widest font-medium mb-4">Tools</p>
            <ul class="flex flex-col gap-2.5">
              <li><button @click="$emit('navigate', 'KeywordColorPalette')" class="text-sm text-gray-400 hover:text-white transition text-left">Keyword Palette</button></li>
              <li><button @click="$emit('navigate', 'ColorPalette')" class="text-sm text-gray-400 hover:text-white transition text-left">Generate from Image</button></li>
              <li><button @click="$emit('navigate', 'CreatePalette')" class="text-sm text-gray-400 hover:text-white transition text-left">Create Palette</button></li>
              <li><button @click="$emit('navigate', 'SavePalette')" class="text-sm text-gray-400 hover:text-white transition text-left">Saved Palettes</button></li>
            </ul>
          </div>

          <div>
            <p class="text-xs text-gray-500 uppercase tracking-widest font-medium mb-4">Community</p>
            <ul class="flex flex-col gap-2.5">
              <li><button @click="$emit('navigate', 'Community')" class="text-sm text-gray-400 hover:text-white transition text-left">Explore</button></li>
              <li><button @click="$emit('navigate', 'SavePost')" class="text-sm text-gray-400 hover:text-white transition text-left">Saved Posts</button></li>
              <li><button @click="$emit('navigate', 'UserProfile')" class="text-sm text-gray-400 hover:text-white transition text-left">My Profile</button></li>
              <li><button @click="$emit('navigate', 'UserSettings')" class="text-sm text-gray-400 hover:text-white transition text-left">Settings</button></li>
            </ul>
          </div>

          <div>
            <p class="text-xs text-gray-500 uppercase tracking-widest font-medium mb-4">Info</p>
            <ul class="flex flex-col gap-2.5">
              <li><button @click="$emit('navigate', 'About')" class="text-sm text-gray-400 hover:text-white transition text-left">About</button></li>
              <li><button @click="$emit('navigate', 'LearnMore')" class="text-sm text-gray-400 hover:text-white transition text-left">Learn More</button></li>
              <li><a href="mailto:hello@palette.app" class="text-sm text-gray-400 hover:text-white transition">Contact</a></li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Divider -->
      <div class="border-t border-white/10 pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-xs text-gray-600">© {{ new Date().getFullYear() }} Palette. Made with ❤️ and color.</p>
        <div class="flex items-center gap-2">
          <div class="flex gap-1">
            <div v-for="c in ['#6366f1','#8b5cf6','#ec4899','#f97316','#eab308','#22c55e']" :key="c"
              class="w-3 h-3 rounded-full" :style="{ backgroundColor: c }"></div>
          </div>
          <span class="text-xs text-gray-600 ml-1">color everything</span>
        </div>
      </div>

    </div>
  </footer>
</template>

<script setup>
import logo from '../assets/Logo-images/Palette-Logo.png'
defineEmits(['navigate'])
</script>