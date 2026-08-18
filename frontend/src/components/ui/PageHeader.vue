<script setup lang="ts">
const { title, description } = defineProps<{ title: string; description?: string }>()
</script>

<template>
  <header class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
      <h1 class="text-xl font-semibold">{{ title }}</h1>
      <p v-if="description" class="text-sm text-gray-500 dark:text-gray-400">{{ description }}</p>
    </div>
    <div v-if="$slots.actions" class="flex gap-2">
      <slot name="actions" />
    </div>
  </header>
</template>
