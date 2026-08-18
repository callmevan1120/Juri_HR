import 'vue-router'

declare module 'vue-router' {
    interface RouteMeta {
        requiresAuth?: boolean
        roles?: string[]
        layout?: 'admin' | 'user' | 'guest'
    }
}
