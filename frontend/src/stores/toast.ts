import { defineStore } from 'pinia'
import { ref } from 'vue'

export type ToastTone = 'success' | 'error' | 'info'

export type Toast = {
    id: number
    message: string
    tone: ToastTone
}

export const useToastStore = defineStore('toast', () => {
    const toasts = ref<Toast[]>([])
    let nextId = 1

    function dismiss(id: number) {
        toasts.value = toasts.value.filter((t) => t.id !== id)
    }

    function push(message: string, tone: ToastTone = 'info', timeout = 4000) {
        const id = nextId++
        toasts.value.push({ id, message, tone })
        if (timeout > 0) setTimeout(() => dismiss(id), timeout)
        return id
    }

    return {
        toasts,
        push,
        dismiss,
        success: (message: string) => push(message, 'success'),
        error: (message: string) => push(message, 'error'),
    }
})
