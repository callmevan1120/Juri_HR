<script setup lang="ts">
import { useToastStore, type ToastTone } from '@/stores/toast'

const toasts = useToastStore()

const TONE_CLASSES: Record<ToastTone, string> = {
    success: 'bg-primary-600 text-white',
    error: 'bg-red-600 text-white',
    info: 'bg-gray-900 text-white dark:bg-gray-700',
}
</script>

<template>
    <div
        class="pointer-events-none fixed inset-x-0 bottom-4 z-50 flex flex-col items-center gap-2 px-4"
        role="status"
        aria-live="polite"
    >
        <div
            v-for="toast in toasts.toasts"
            :key="toast.id"
            class="pointer-events-auto flex w-full max-w-sm items-center justify-between gap-3 rounded-lg px-4 py-3 text-sm shadow-lg"
            :class="TONE_CLASSES[toast.tone]"
        >
            <span>{{ toast.message }}</span>
            <button
                type="button"
                class="shrink-0 opacity-80 hover:opacity-100"
                aria-label="Tutup notifikasi"
                @click="toasts.dismiss(toast.id)"
            >
                &times;
            </button>
        </div>
    </div>
</template>
