<script setup lang="ts">
import { useId } from 'vue'

const {
    label,
    type = 'text',
    error,
    hint,
    required = false,
} = defineProps<{
    label?: string
    type?: string
    error?: string
    hint?: string
    required?: boolean
}>()

const model = defineModel<string | number>()
const id = useId()

defineOptions({ inheritAttrs: false })
</script>

<template>
    <div class="space-y-1">
        <label v-if="label" :for="id" class="block text-sm font-medium">
            {{ label }}
            <span v-if="required" class="text-red-600" aria-hidden="true">*</span>
        </label>
        <input
            :id="id"
            v-model="model"
            v-bind="$attrs"
            :type="type"
            :required="required"
            :aria-invalid="error ? 'true' : undefined"
            :aria-describedby="error || hint ? `${id}-desc` : undefined"
            class="field-base"
            :class="error ? 'border-red-500 dark:border-red-500' : ''"
        />
        <p
            v-if="error || hint"
            :id="`${id}-desc`"
            class="text-xs"
            :class="error ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400'"
        >
            {{ error || hint }}
        </p>
    </div>
</template>
