import {
    createRouter,
    createWebHistory,
    type NavigationGuardWithThis,
    type RouteRecordRaw,
} from 'vue-router'
import { HRD_ROLES, useAuthStore } from '@/stores/auth'

const HRD_ONLY = HRD_ROLES

const routes: RouteRecordRaw[] = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/LoginView.vue'),
        meta: { layout: 'guest' },
    },
    {
        path: '/403',
        name: 'forbidden',
        component: () => import('@/views/ForbiddenView.vue'),
        meta: { layout: 'guest' },
    },

    // Employee area
    {
        path: '/',
        name: 'home',
        component: () => import('@/views/user/HomeView.vue'),
        meta: { requiresAuth: true, layout: 'user' },
    },
    {
        path: '/kalender',
        name: 'calendar',
        component: () => import('@/views/user/CalendarView.vue'),
        meta: { requiresAuth: true, layout: 'user' },
    },
    {
        path: '/pengajuan',
        name: 'my-requests',
        component: () => import('@/views/user/RequestsView.vue'),
        meta: { requiresAuth: true, layout: 'user' },
    },
    {
        path: '/slip-gaji',
        name: 'my-payslips',
        component: () => import('@/views/user/PayslipsView.vue'),
        meta: { requiresAuth: true, layout: 'user' },
    },
    {
        path: '/profil',
        name: 'profile',
        component: () => import('@/views/user/ProfileView.vue'),
        meta: { requiresAuth: true, layout: 'user' },
    },

    // HRD area
    {
        path: '/admin',
        name: 'admin-dashboard',
        component: () => import('@/views/admin/DashboardView.vue'),
        meta: { requiresAuth: true, roles: HRD_ONLY, layout: 'admin' },
    },
    {
        path: '/admin/karyawan',
        name: 'admin-employees',
        component: () => import('@/views/admin/EmployeesView.vue'),
        meta: { requiresAuth: true, roles: HRD_ONLY, layout: 'admin' },
    },
    {
        path: '/admin/absensi',
        name: 'admin-attendance',
        component: () => import('@/views/admin/AttendanceView.vue'),
        meta: { requiresAuth: true, roles: HRD_ONLY, layout: 'admin' },
    },
    {
        path: '/admin/jadwal',
        name: 'admin-schedule',
        component: () => import('@/views/admin/ScheduleView.vue'),
        meta: { requiresAuth: true, roles: HRD_ONLY, layout: 'admin' },
    },
    {
        path: '/admin/pengajuan',
        name: 'admin-requests',
        component: () => import('@/views/admin/RequestsView.vue'),
        meta: { requiresAuth: true, roles: HRD_ONLY, layout: 'admin' },
    },
    {
        path: '/admin/slip-gaji',
        name: 'admin-payslips',
        component: () => import('@/views/admin/PayslipsView.vue'),
        meta: { requiresAuth: true, roles: HRD_ONLY, layout: 'admin' },
    },
    {
        path: '/admin/pengumuman',
        name: 'admin-announcements',
        component: () => import('@/views/admin/AnnouncementsView.vue'),
        meta: { requiresAuth: true, roles: HRD_ONLY, layout: 'admin' },
    },
    {
        path: '/admin/aktivitas',
        name: 'admin-activity',
        component: () => import('@/views/admin/ActivityView.vue'),
        meta: { requiresAuth: true, roles: HRD_ONLY, layout: 'admin' },
    },
    {
        path: '/admin/pengaturan',
        name: 'admin-settings',
        component: () => import('@/views/admin/SettingsView.vue'),
        meta: { requiresAuth: true, roles: HRD_ONLY, layout: 'admin' },
    },

    { path: '/:pathMatch(.*)*', redirect: '/' },
]

if (import.meta.env.DEV) {
    routes.unshift({
        path: '/__dev/ui',
        name: 'dev-ui',
        component: () => import('@/views/DevUiView.vue'),
        meta: { layout: 'guest' },
    })
}

export const authGuard: NavigationGuardWithThis<undefined> = async (to) => {
    const auth = useAuthStore()

    if (!to.meta.requiresAuth) {
        if (to.name === 'login' && auth.isAuthenticated) return auth.isHrd ? '/admin' : '/'
        return true
    }

    if (!auth.isAuthenticated) {
        try {
            await auth.loadSession()
        } catch {
            // fall through to the login redirect below
        }
    }

    if (!auth.isAuthenticated) return { name: 'login', query: { redirect: to.fullPath } }

    const allowed = to.meta.roles
    if (allowed?.length && !auth.roles.some((role) => allowed.includes(role))) {
        return { name: 'forbidden' }
    }

    return true
}

export const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach(authGuard)
