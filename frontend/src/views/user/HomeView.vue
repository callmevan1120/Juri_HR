<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import AppEmptyState from '@/components/ui/AppEmptyState.vue'
import PageHeader from '@/components/ui/PageHeader.vue'

const auth = useAuthStore()
</script>

<template>
    <div>
        <PageHeader
            :title="`Halo, ${auth.session?.full_name ?? 'Karyawan'}`"
            description="Absensi hari ini akan muncul di sini"
        />
        <AppEmptyState
            title="Belum tersedia"
            description="Check-in dan check-out akan dibangun pada fase M2."
        />
    </div>
</template>
