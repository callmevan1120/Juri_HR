/// <reference types="vite/client" />

interface ImportMetaEnv {
    readonly VITE_FRAPPE_BASE_URL: string
    readonly VITE_USE_FIXTURES: string
    readonly VITE_APP_NAME: string
}

interface ImportMeta {
    readonly env: ImportMetaEnv
}
