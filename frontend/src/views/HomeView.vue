<script setup lang="ts">
const appName = import.meta.env.VITE_APP_NAME || 'JuriHR'
</script>

<template>
  <main class="grid min-h-svh place-items-center gap-4 p-6 text-center">
    <div class="space-y-2">
      <h1 class="text-primary-600 dark:text-primary-400 text-2xl font-semibold">{{ appName }}</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400">Platform HR — MVP dalam pengembangan</p>
    </div>
  </main>
</template>
