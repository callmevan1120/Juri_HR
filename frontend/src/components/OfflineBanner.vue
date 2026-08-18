<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'

const online = ref(true)

function sync() {
    online.value = navigator.onLine
}

onMounted(() => {
    sync()
    window.addEventListener('online', sync)
    window.addEventListener('offline', sync)
})

onUnmounted(() => {
    window.removeEventListener('online', sync)
    window.removeEventListener('offline', sync)
})
</script>

<template>
    <p
        v-if="!online"
        role="status"
        class="bg-amber-100 px-4 py-1.5 text-center text-xs font-medium text-amber-900 dark:bg-amber-900/50 dark:text-amber-100"
    >
        Mode offline — perubahan tidak akan tersimpan sampai koneksi kembali.
    </p>
</template>
