<script setup lang="ts">
import { useId } from 'vue'

const {
    label,
    options,
    error,
    required = false,
    placeholder,
} = defineProps<{
    label?: string
    options: { value: string | number; label: string }[]
    error?: string
    required?: boolean
    placeholder?: string
}>()

const model = defineModel<string | number>()
const id = useId()
</script>

<template>
    <div class="space-y-1">
        <label v-if="label" :for="id" class="block text-sm font-medium">
            {{ label }}
            <span v-if="required" class="text-red-600" aria-hidden="true">*</span>
        </label>
        <select
            :id="id"
            v-model="model"
            :required="required"
            :aria-invalid="error ? 'true' : undefined"
            class="field-base"
            :class="error ? 'border-red-500 dark:border-red-500' : ''"
        >
            <option v-if="placeholder" value="">{{ placeholder }}</option>
            <option v-for="opt in options" :key="opt.value" :value="opt.value">
                {{ opt.label }}
            </option>
        </select>
        <p v-if="error" class="text-xs text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
</template>
