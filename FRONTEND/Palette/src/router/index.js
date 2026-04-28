// src/router/index.js

import { createRouter, createWebHistory } from 'vue-router'
import KeywordColorPalette from '../components/KeywordColorPalette.vue'
import ColorPalette        from '../components/ColorPalette.vue'
import CreatePalette       from '../components/CreatePalette.vue'
import SavePalette         from '../components/SavePalette.vue'
import Heroes              from '../components/Heroes.vue'

const routes = [
  { path: '/',               component: Heroes              },
  { path: '/keyword',        component: KeywordColorPalette },
  { path: '/generate-image', component: ColorPalette        },
  { path: '/create-palette', component: CreatePalette       },
  { path: '/save-palette',   component: SavePalette         },
]

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});

export default createRouter({
  history: createWebHistory(),
  routes,
})