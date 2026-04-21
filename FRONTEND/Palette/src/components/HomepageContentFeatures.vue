<template>
  <!--HomepageContentFeatures.vue-->

  <section class="features-section">
    <div
      v-for="(feature, index) in features"
      :key="feature.id"
      class="feature"
      :class="{ reverse: index % 2 !== 0 }"
    >
      <div class="feature-text">
        <h2 class="feature-title" v-html="feature.title" />
        <p class="feature-desc">{{ feature.description }}</p>
      </div>

      <div class="feature-visual">
        
        <slot :name="feature.id" />
      </div>
    </div>
  </section>
</template>

<script setup>
const features = [
  {
    id: 'keyword',
    title: 'Keyword',
    description:
      'The Keyword feature lets users type a word like fire, calm, nature, or any mood/theme they want. The system will generate a color palette based on the feeling or vibe of that word, making it easy to find matching colors for ideas and designs.',
  },
  {
    id: 'generate',
    title: 'Generate',
    description:
      'The Generate feature allows users to upload an image, and the website will automatically create a color palette inspired by the colors found in that image. This is useful for getting color ideas from photos, artwork, or any visual inspiration.',
  },
  {
    id: 'create',
    title: 'Create',
    description:
      'The Create feature gives users full control to make their own custom color palette. They can manually enter HEX, RGB, or HSL color values to build exact color combinations they want for their projects.',
  },
  {
    id: 'save-palette',
    title: 'Save<br>Palette',
    description:
      'The Save Palette feature helps users keep their favorite palettes for later use. Once a palette is saved, they can easily come back to it anytime without needing to recreate it again.',
  },
]
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@900&family=DM+Sans:wght@400;500&display=swap');

.features-section {
  max-width: 1280px;
  margin: 0 auto;
  padding: 80px 48px;
}

/* ── Single feature row ── */
.feature {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 64px;
  align-items: center;
  padding: 80px 0;
  border-bottom: 1px solid #e8e8e8;
}

.feature:last-child {
  border-bottom: none;
}

/* Flip order: visual moves to the left */
.feature.reverse .feature-visual {
  order: -1;
}

/* ── Text side ── */
.feature-title {
  font-family: 'Barlow Condensed', Impact, sans-serif;
  font-weight: 900;
  font-size: clamp(72px, 9vw, 120px);
  line-height: 0.9;
  letter-spacing: -0.02em;
  text-transform: uppercase;
  margin-bottom: 24px;
}

.feature-desc {
  font-family: 'DM Sans', sans-serif;
  font-size: 18px;
  line-height: 1.8;
  max-width: 480px;
  font-weight: 400;
}

/* ── Visual placeholder ── */
.feature-visual {
  background: #b8b8b8;
  border-radius: 24px;
  display: flex;
  align-items: flex-start;
  justify-content: flex-start;
  position: relative;
  overflow: hidden;
  background-size: cover;
  background-attachment: fixed;
  background-position: center;
}

.feature-number {
  font-family: 'DM Sans', sans-serif;
  font-size: 11px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #6b6b6b;
}

/* ── Responsive ── */
@media (max-width: 1024px) {
  .features-section {
    padding: 64px 32px;
  }

  .feature {
    gap: 40px;
    padding: 64px 0;
  }
}

@media (max-width: 768px) {
  .features-section {
    padding: 48px 24px;
  }

  .feature {
    grid-template-columns: 1fr;
    gap: 28px;
    padding: 48px 0;
  }

  /* Reset flip on mobile — always text first, visual second */
  .feature.reverse .feature-visual {
    order: 0;
  }

  .feature-title {
    font-size: 72px;
  }

  .feature-desc {
    font-size: 16px;
  }
}

/* Dark mode overrides */
:global(.dark) .feature-title {
  color: #ffffff;

}

:global(.dark) .feature-desc {
  color: #c0c4ca;
}

:global(.dark) .feature {
  border-bottom-color: #374151;
}

:global(.dark) .feature-visual {
  background: #374151;
}

:global(.dark) .features-section {
  background: #111827;
}

:global(.dark) .feature-number {
  color: #ffffff;
}
</style>