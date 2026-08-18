<script setup lang="ts">
import AppEmptyState from '@/components/ui/AppEmptyState.vue'
import PageHeader from '@/components/ui/PageHeader.vue'

const { title, description, phase } = defineProps<{
    title: string
    description?: string
    phase: string
}>()
</script>

<template>
    <div>
        <PageHeader :title="title" :description="description" />
        <AppEmptyState title="Belum tersedia" :description="`Akan dibangun pada fase ${phase}.`" />
    </div>
</template>
