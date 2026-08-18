<script setup lang="ts">
import { onUnmounted, ref, watch } from 'vue'

const { title, open } = defineProps<{ title: string; open: boolean }>()
const emit = defineEmits<{ close: [] }>()

const dialog = ref<HTMLDialogElement | null>(null)

watch(
  () => open,
  (isOpen) => {
    if (!dialog.value) return
    if (isOpen && !dialog.value.open) dialog.value.showModal()
    if (!isOpen && dialog.value.open) dialog.value.close()
  },
)

onUnmounted(() => dialog.value?.close())
</script>

<template>
  <dialog
    ref="dialog"
    class="card m-auto w-[min(32rem,calc(100vw-2rem))] p-0 backdrop:bg-black/50"
    :aria-label="title"
    @close="emit('close')"
    @cancel.prevent="emit('close')"
  >
    <div class="flex items-start justify-between gap-4 border-b border-gray-200 p-4 dark:border-gray-800">
      <h2 class="text-base font-semibold">{{ title }}</h2>
      <button
        type="button"
        class="tap-target rounded-lg px-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"
        aria-label="Tutup"
        @click="emit('close')"
      >
        &times;
      </button>
    </div>
    <div class="p-4">
      <slot />
    </div>
    <div v-if="$slots.footer" class="flex justify-end gap-2 border-t border-gray-200 p-4 dark:border-gray-800">
      <slot name="footer" />
    </div>
  </dialog>
</template>
