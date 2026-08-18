<script setup lang="ts">
import { ref } from 'vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import AppEmptyState from '@/components/ui/AppEmptyState.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppSpinner from '@/components/ui/AppSpinner.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import DataTable from '@/components/ui/DataTable.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import { DAILY_STATUSES } from '@/components/ui/status'
import type { Column } from '@/components/ui/table'
import { useThemeStore } from '@/stores/theme'
import { useToastStore } from '@/stores/toast'

const theme = useThemeStore()
const toasts = useToastStore()

const text = ref('Budi Santoso')
const note = ref('')
const division = ref('')
const agreed = ref(false)
const modalOpen = ref(false)

type Row = { nip: string; name: string; status: string }

const columns: Column<Row>[] = [
    { key: 'nip', label: 'NIP', sortable: true },
    { key: 'name', label: 'Nama', sortable: true },
    { key: 'status', label: 'Status' },
]

const rows: Row[] = [
    { nip: '001', name: 'Budi Santoso', status: 'present' },
    { nip: '002', name: 'Sari Dewi', status: 'late' },
    { nip: '003', name: 'Andi Wijaya', status: 'izin' },
]
</script>

<template>
    <div class="mx-auto max-w-4xl space-y-8 p-6">
        <PageHeader title="UI Kit" description="Halaman dev untuk semua komponen dasar">
            <template #actions>
                <AppButton variant="secondary" @click="theme.toggle">
                    Mode: {{ theme.theme === 'dark' ? 'Gelap' : 'Terang' }}
                </AppButton>
            </template>
        </PageHeader>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold uppercase text-gray-500">Tombol</h2>
            <div class="flex flex-wrap items-center gap-2">
                <AppButton>Primary</AppButton>
                <AppButton variant="secondary">Secondary</AppButton>
                <AppButton variant="danger">Danger</AppButton>
                <AppButton variant="ghost">Ghost</AppButton>
                <AppButton size="sm">Kecil</AppButton>
                <AppButton loading>Memuat</AppButton>
                <AppButton disabled>Nonaktif</AppButton>
                <AppSpinner />
            </div>
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold uppercase text-gray-500">Form</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <AppInput v-model="text" label="Nama" required hint="Sesuai KTP" />
                <AppInput
                    v-model="text"
                    label="Email"
                    type="email"
                    error="Format email tidak valid"
                />
                <AppSelect
                    v-model="division"
                    label="Divisi"
                    placeholder="Pilih divisi"
                    :options="[
                        { value: 'hrd', label: 'HRD' },
                        { value: 'outlet', label: 'Outlet' },
                    ]"
                />
                <AppTextarea v-model="note" label="Catatan" hint="Opsional" />
            </div>
            <AppCheckbox v-model="agreed" label="Saya menyetujui kebijakan absensi" />
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold uppercase text-gray-500">Status</h2>
            <div class="flex flex-wrap gap-2">
                <AppBadge v-for="status in DAILY_STATUSES" :key="status" :status="status" />
            </div>
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold uppercase text-gray-500">Tabel</h2>
            <DataTable :columns="columns" :rows="rows" :page-size="2">
                <template #cell-status="{ row }">
                    <AppBadge :status="row.status" />
                </template>
            </DataTable>
            <DataTable :columns="columns" :rows="[]" empty-title="Belum ada absensi hari ini" />
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold uppercase text-gray-500">Overlay</h2>
            <div class="flex flex-wrap gap-2">
                <AppButton @click="modalOpen = true">Buka modal</AppButton>
                <AppButton variant="secondary" @click="toasts.success('Absensi tersimpan')">
                    Toast sukses
                </AppButton>
                <AppButton variant="danger" @click="toasts.error('Di luar radius kantor')">
                    Toast error
                </AppButton>
            </div>
            <AppEmptyState
                title="Tidak ada pengajuan"
                description="Semua pengajuan sudah diproses"
            />
            <AppModal title="Konfirmasi" :open="modalOpen" @close="modalOpen = false">
                <p class="text-sm">Setujui koreksi absensi karyawan ini?</p>
                <template #footer>
                    <AppButton variant="secondary" @click="modalOpen = false">Batal</AppButton>
                    <AppButton @click="modalOpen = false">Setujui</AppButton>
                </template>
            </AppModal>
        </section>
    </div>
</template>
