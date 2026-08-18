import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/HomeView.vue'),
  },
]

if (import.meta.env.DEV) {
  routes.push({
    path: '/__dev/ui',
    name: 'dev-ui',
    component: () => import('@/views/DevUiView.vue'),
  })
}

export const router = createRouter({
  history: createWebHistory(),
  routes,
})
