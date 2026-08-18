<script setup lang="ts">
import AppToast from '@/components/ui/AppToast.vue'
import { useThemeStore } from '@/stores/theme'

useThemeStore()
</script>

<template>
  <RouterView />
  <AppToast />
</template>
