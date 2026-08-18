<script setup lang="ts" generic="T extends Record<string, unknown>">
import { computed, ref } from 'vue'
import AppEmptyState from './AppEmptyState.vue'
import type { Column } from './table'

const {
  columns,
  rows,
  pageSize = 10,
  emptyTitle = 'Belum ada data',
} = defineProps<{
  columns: Column<T>[]
  rows: T[]
  pageSize?: number
  emptyTitle?: string
}>()

const sortKey = ref<string | null>(null)
const sortAsc = ref(true)
const page = ref(1)

const sorted = computed(() => {
  if (!sortKey.value) return rows
  const key = sortKey.value
  return [...rows].sort((a, b) => {
    const av = a[key]
    const bv = b[key]
    if (av === bv) return 0
    const result = (av as never) > (bv as never) ? 1 : -1
    return sortAsc.value ? result : -result
  })
})

const pageCount = computed(() => Math.max(1, Math.ceil(sorted.value.length / pageSize)))
const paged = computed(() => sorted.value.slice((page.value - 1) * pageSize, page.value * pageSize))

function toggleSort(column: Column<T>) {
  if (!column.sortable) return
  if (sortKey.value === column.key) {
    sortAsc.value = !sortAsc.value
    return
  }
  sortKey.value = column.key
  sortAsc.value = true
}

function ariaSort(column: Column<T>) {
  if (!column.sortable) return undefined
  if (sortKey.value !== column.key) return 'none'
  return sortAsc.value ? 'ascending' : 'descending'
}
</script>

<template>
  <AppEmptyState v-if="!rows.length" :title="emptyTitle" />
  <div v-else class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left dark:bg-gray-800/60">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              scope="col"
              class="px-4 py-3 font-medium"
              :class="column.align === 'right' ? 'text-right' : ''"
              :aria-sort="ariaSort(column)"
            >
              <button
                v-if="column.sortable"
                type="button"
                class="inline-flex items-center gap-1 font-medium"
                @click="toggleSort(column)"
              >
                {{ column.label }}
                <span aria-hidden="true">{{
                  sortKey === column.key ? (sortAsc ? '↑' : '↓') : '↕'
                }}</span>
              </button>
              <template v-else>{{ column.label }}</template>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
          <tr v-for="(row, index) in paged" :key="index">
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-4 py-3"
              :class="column.align === 'right' ? 'text-right' : ''"
            >
              <slot :name="`cell-${column.key}`" :row="row">{{ row[column.key] }}</slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div
      v-if="pageCount > 1"
      class="flex items-center justify-between gap-2 border-t border-gray-200 px-4 py-3 text-sm dark:border-gray-800"
    >
      <span class="text-gray-500 dark:text-gray-400">Halaman {{ page }} dari {{ pageCount }}</span>
      <div class="flex gap-2">
        <button
          type="button"
          class="tap-target rounded-lg px-3 disabled:opacity-50"
          :disabled="page === 1"
          @click="page--"
        >
          Sebelumnya
        </button>
        <button
          type="button"
          class="tap-target rounded-lg px-3 disabled:opacity-50"
          :disabled="page === pageCount"
          @click="page++"
        >
          Berikutnya
        </button>
      </div>
    </div>
  </div>
</template>
