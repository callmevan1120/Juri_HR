<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppButton from '@/components/ui/AppButton.vue'
import AppInput from '@/components/ui/AppInput.vue'
import { useAuthStore } from '@/stores/auth'
import type { ApiError } from '@/api/client'
import { useFixtures } from '@/api/fixtures'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const fixtureMode = useFixtures()

const email = ref('')
const password = ref('')
const error = ref('')

async function submit() {
    error.value = ''
    try {
        await auth.login(email.value, password.value)
        const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : null
        await router.replace(redirect ?? (auth.isHrd ? '/admin' : '/'))
    } catch (e) {
        error.value = (e as ApiError).message || 'Gagal masuk. Coba lagi.'
    }
}
</script>

<template>
    <div class="grid min-h-svh place-items-center p-6">
        <form class="card w-full max-w-sm space-y-4 p-6" @submit.prevent="submit">
            <div class="space-y-1 text-center">
                <img src="/logo.png" alt="JuriHR" class="mx-auto h-12 w-auto" />
                <h1 class="text-lg font-semibold">Masuk ke JuriHR</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Gunakan akun karyawan Anda</p>
            </div>

            <AppInput v-model="email" label="Email" type="email" required autocomplete="username" />
            <AppInput
                v-model="password"
                label="Kata sandi"
                type="password"
                required
                autocomplete="current-password"
            />

            <p v-if="error" role="alert" class="text-sm text-red-600 dark:text-red-400">
                {{ error }}
            </p>

            <AppButton type="submit" block :loading="auth.loading">Masuk</AppButton>

            <p
                v-if="fixtureMode"
                class="rounded-lg bg-amber-50 p-3 text-xs text-amber-900 dark:bg-amber-900/30 dark:text-amber-100"
            >
                Mode fixture: kata sandi apa pun diterima. Gunakan email yang diawali
                <code>hrd</code> (mis. <code>hrd@example.com</code>) untuk masuk sebagai HRD, email
                lain (mis. <code>budi@example.com</code>) untuk masuk sebagai karyawan.
            </p>
        </form>
    </div>
</template>
