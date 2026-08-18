<script setup lang="ts">
import { computed } from 'vue'
import { BADGE_TONE_CLASSES, statusLabel, statusTone, type BadgeTone } from './status'

const { tone, status } = defineProps<{ tone?: BadgeTone; status?: string }>()

const toneClass = computed(
  () => BADGE_TONE_CLASSES[tone ?? (status ? statusTone(status) : 'gray')],
)
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
    :class="toneClass"
  >
    <slot>{{ status ? statusLabel(status) : '' }}</slot>
  </span>
</template>
