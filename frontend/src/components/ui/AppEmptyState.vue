<script setup lang="ts">
const { title, description } = defineProps<{ title: string; description?: string }>()
</script>

<template>
  <div class="card flex flex-col items-center gap-2 p-8 text-center">
    <p class="font-medium">{{ title }}</p>
    <p v-if="description" class="text-sm text-gray-500 dark:text-gray-400">{{ description }}</p>
    <slot />
  </div>
</template>
