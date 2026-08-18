<script setup lang="ts">
const { label = '' } = defineProps<{ label?: string }>()
</script>

<template>
  <svg
    class="size-5 animate-spin text-current"
    viewBox="0 0 24 24"
    fill="none"
    role="status"
    :aria-label="label || 'Memuat'"
  >
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
    <path
      class="opacity-75"
      fill="currentColor"
      d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
    />
  </svg>
</template>
