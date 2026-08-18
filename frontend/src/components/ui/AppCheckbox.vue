<script setup lang="ts">
import { useId } from 'vue'

const { label } = defineProps<{ label: string }>()

const model = defineModel<boolean>()
const id = useId()
</script>

<template>
    <div class="tap-target flex items-center gap-2">
        <input
            :id="id"
            v-model="model"
            type="checkbox"
            class="text-primary-600 focus:ring-primary-500 size-5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
        />
        <label :for="id" class="text-sm">{{ label }}</label>
    </div>
</template>
