const TIME_ZONE = 'Asia/Jakarta'

const rupiah = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
})

const longDate = new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'long',
    timeZone: TIME_ZONE,
})

const dateTime = new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'long',
    timeStyle: 'short',
    timeZone: TIME_ZONE,
})

export function formatCurrency(value: number | null | undefined): string {
    if (value === null || value === undefined || Number.isNaN(value)) return '-'
    return rupiah.format(value)
}

/** Accepts Frappe date strings (`YYYY-MM-DD` / `YYYY-MM-DD HH:mm:ss`). */
export function formatDate(value: string | null | undefined): string {
    const date = parseFrappeDate(value)
    return date ? longDate.format(date) : '-'
}

export function formatDateTime(value: string | null | undefined): string {
    const date = parseFrappeDate(value)
    return date ? dateTime.format(date) : '-'
}

/** Frappe stores times as `HH:mm:ss`; the UI shows `HH:mm`. */
export function formatTime(value: string | null | undefined): string {
    if (!value) return '-'
    const match = value.match(/(\d{2}):(\d{2})/)
    return match ? `${match[1]}:${match[2]}` : '-'
}

export function formatMinutes(total: number | null | undefined): string {
    if (total === null || total === undefined || Number.isNaN(total)) return '-'
    const sign = total < 0 ? '-' : ''
    const minutes = Math.abs(Math.round(total))
    const hours = Math.floor(minutes / 60)
    const rest = minutes % 60
    if (!hours) return `${sign}${rest}m`
    return `${sign}${hours}j ${rest}m`
}

function parseFrappeDate(value: string | null | undefined): Date | null {
    if (!value) return null
    const normalized = value.includes('T') ? value : value.replace(' ', 'T')
    const date = new Date(normalized)
    return Number.isNaN(date.getTime()) ? null : date
}
