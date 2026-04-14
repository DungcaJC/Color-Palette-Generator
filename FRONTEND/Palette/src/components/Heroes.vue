<template>
  <!--Heroes.vue-->

  <!-- Root: full screen dark background -->
  <div class="mainbg flex h-screen w-full bg-[#0d1117] overflow-hidden font-sans">

    <!-- ── LEFT HERO SECTION ── -->
    <div class="flex flex-col justify-center px-16 w-1/2 relative">

      <!-- Background subtle grid overlay -->
      <div class="absolute inset-0 opacity-10"
           :style="{
             backgroundImage: 'linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px)',
             backgroundSize: '40px 40px'
           }">
      </div>

      <!-- Logo -->
      <div class="mb-10 relative z-10">
        <div class="w-8 h-8 rounded-full flex items-center justify-center shadow-lg shadow-yellow">
          <img class="h-8 w-auto" src="../assets/Logo-images/Palette-Logo.png" alt="Palette Logo" />
        </div>
      </div>

      <!-- Badge row -->
      <div class="flex items-center gap-3 mb-6 relative z-10">
        <span class="text-xs text-white bg-white/10 border border-white/10 rounded-full px-3 py-1">
          What's new
        </span>
        <span class="text-xs text-slate-300 bg-white/5 border border-white/10 rounded-full px-3 py-1 flex items-center gap-1">
          Just shipped v1.0 <span class="ml-1 text-slate-400">›</span>
        </span>
      </div>

      <!-- Hero heading -->
      <h1 class="text-5xl font-bold text-white leading-tight mb-5 relative z-10">
        Create Stunning Color Palettes That Instantly <br>Elevate Your Designs
      </h1>

      <!-- Subtext -->
      <p class="text-white text-sm leading-relaxed max-w-sm mb-8 relative z-10">
        Explore, mix, and match colors easily to bring your ideas to life without overthinking every shade
      </p>

      <!-- CTA buttons -->
      <div class="flex items-center gap-4 relative z-10">
        <button id="generate-btn" class="text-white text-sm font-medium px-5 py-2.5 rounded-md">
          <a href="#Generate-image">Generate</a>
        </button>
        <button class="text-slate-300 hover:text-white transition-colors text-sm flex items-center gap-1">
          Learn more <span>→</span>
        </button>
      </div>
    </div>

    <!-- ── RIGHT PANEL (App mockup) ── -->
    <div class="w-1/2 flex items-center justify-center p-8">
      <img class="h-auto w-auto rounded-lg shadow-lg relative z-10 shadow-black/10" src="../assets/Bg-Images/ColorPalette3694.png" alt="Color-Palette-#3694" />
    </div>

  </div>
  <div>
    <!-- Content -->
    <HomepageContentFeatures>
      <template #keyword>
        <img src="../assets/EMOJI/JOY.png" alt="Keyword feature" />
      </template>
      <template #generate>
        <img src="../assets/EMOJI/SAD.png" alt="Generate feature" />
      </template>
      <template #create>
        <img src="../assets/EMOJI/FEAR.png" alt="Create feature" />
      </template>
      <template #save-palette>
        <img src="../assets/EMOJI/ANGRY.png" alt="Save Palette feature" />
      </template>
    </HomepageContentFeatures>
  </div>
</template>

<script setup>
import '../assets/Logo-images/Palette-Logo.png'
import '../assets/Bg-Images/ColorPalette3694.png'
import '../assets/style.css'
import HomepageContentFeatures from './HomepageContentFeatures.vue'

const navItems = [
  { label: 'Projects',     icon: '□', active: false },
  { label: 'Deployments',  icon: '⊙', active: true  },
  { label: 'Activity',     icon: '≋', active: false },
  { label: 'Domains',      icon: '◈', active: false },
  { label: 'Usage',        icon: '◻', active: false },
  { label: 'Settings',     icon: '⚙', active: false },
]

const teams = ['Planetaria', 'Protocol', 'Tailwind Labs']

const projects = [
  { name: 'Planetaria / ios-app',       sub: 'Deploys from GitHub · Initiated',  status: 'pending'  },
  { name: 'Planetaria / mobile-api',    sub: 'Deploys from GitHub · Deploy…',    status: 'success'  },
  { name: 'Tailwind Labs / tailwi…',    sub: 'Deploys from GitHub · Initiated',  status: 'pending'  },
  { name: 'Tailwind Labs / tailwi…',    sub: 'Deploys from GitHub · Initiated',  status: 'pending'  },
  { name: 'Protocol / relay-service',   sub: 'Deploys from GitHub · Deploy…',    status: 'success'  },
  { name: 'Planetaria / android-app',   sub: 'Deploys from GitHub · Deploy…',    status: 'success'  },
  { name: 'Protocol / api.protocol.cl', sub: 'Deploys from GitHub · Failed to…', status: 'error'    },
]
</script>