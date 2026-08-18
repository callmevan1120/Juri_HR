<script setup lang="ts">
import { useId } from 'vue'

const {
  label,
  rows = 3,
  error,
  hint,
  required = false,
} = defineProps<{
  label?: string
  rows?: number
  error?: string
  hint?: string
  required?: boolean
}>()

const model = defineModel<string>()
const id = useId()
</script>

<template>
  <div class="space-y-1">
    <label v-if="label" :for="id" class="block text-sm font-medium">
      {{ label }}
      <span v-if="required" class="text-red-600" aria-hidden="true">*</span>
    </label>
    <textarea
      :id="id"
      v-model="model"
      :rows="rows"
      :required="required"
      :aria-invalid="error ? 'true' : undefined"
      :aria-describedby="error || hint ? `${id}-desc` : undefined"
      class="field-base"
      :class="error ? 'border-red-500 dark:border-red-500' : ''"
    ></textarea>
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
