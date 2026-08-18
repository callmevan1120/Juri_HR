import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { createMemoryHistory, createRouter, type Router } from 'vue-router'
import { authGuard } from './index'
import { useAuthStore } from '@/stores/auth'

/**
 * The real guard is mounted on a stub route table so the assertions cover the
 * redirect logic instead of the page components.
 */
function buildRouter(): Router {
    const stub = { template: '<div />' }
    const router = createRouter({
        history: createMemoryHistory(),
        routes: [
            { path: '/login', name: 'login', component: stub, meta: { layout: 'guest' } },
            { path: '/403', name: 'forbidden', component: stub, meta: { layout: 'guest' } },
            {
                path: '/',
                name: 'home',
                component: stub,
                meta: { requiresAuth: true, layout: 'user' },
            },
            {
                path: '/admin',
                name: 'admin-dashboard',
                component: stub,
                meta: { requiresAuth: true, roles: ['HR Manager'], layout: 'admin' },
            },
        ],
    })
    router.beforeEach(authGuard)
    return router
}

function signIn(roles: string[]) {
    const auth = useAuthStore()
    auth.session = { user: 'a@b.c', full_name: 'Tester', roles }
    return auth
}

describe('authGuard', () => {
    let router: Router

    beforeEach(() => {
        setActivePinia(createPinia())
        localStorage.clear()
        router = buildRouter()
    })

    it('sends an anonymous visitor to /login and remembers the target', async () => {
        await router.push('/admin')
        expect(router.currentRoute.value.name).toBe('login')
        expect(router.currentRoute.value.query.redirect).toBe('/admin')
    })

    it('sends a signed-in employee to /403 on an HRD route', async () => {
        signIn(['Employee'])
        await router.push('/admin')
        expect(router.currentRoute.value.name).toBe('forbidden')
    })

    it('lets HRD through to the admin route', async () => {
        signIn(['HR Manager', 'Employee'])
        await router.push('/admin')
        expect(router.currentRoute.value.name).toBe('admin-dashboard')
    })

    it('keeps a signed-in user away from /login', async () => {
        signIn(['Employee'])
        await router.push('/login')
        expect(router.currentRoute.value.name).toBe('home')
    })

    it('restores a session from a stored token before redirecting', async () => {
        const auth = useAuthStore()
        vi.spyOn(auth, 'loadSession').mockImplementation(async () => {
            auth.session = { user: 'a@b.c', full_name: 'Tester', roles: ['Employee'] }
            return auth.session
        })

        await router.push('/')

        expect(auth.loadSession).toHaveBeenCalledOnce()
        expect(router.currentRoute.value.name).toBe('home')
    })
})
