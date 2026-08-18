<script setup lang="ts">
import { computed } from 'vue'
import AppSpinner from './AppSpinner.vue'

type Variant = 'primary' | 'secondary' | 'danger' | 'ghost'
type Size = 'sm' | 'md'

const {
    variant = 'primary',
    size = 'md',
    type = 'button',
    loading = false,
    disabled = false,
    block = false,
} = defineProps<{
    variant?: Variant
    size?: Size
    type?: 'button' | 'submit' | 'reset'
    loading?: boolean
    disabled?: boolean
    block?: boolean
}>()

const VARIANTS: Record<Variant, string> = {
    primary:
        'bg-primary-600 text-white hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600',
    secondary:
        'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800',
    danger: 'bg-red-600 text-white hover:bg-red-700',
    ghost: 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800',
}

const SIZES: Record<Size, string> = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-sm',
}

const classes = computed(() => [
    'tap-target inline-flex items-center justify-center gap-2 rounded-lg font-medium transition disabled:cursor-not-allowed disabled:opacity-60',
    VARIANTS[variant],
    SIZES[size],
    block ? 'w-full' : '',
])
</script>

<template>
    <button :type="type" :class="classes" :disabled="disabled || loading">
        <AppSpinner v-if="loading" class="size-4" />
        <slot />
    </button>
</template>
