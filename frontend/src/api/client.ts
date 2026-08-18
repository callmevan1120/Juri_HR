import { resolveFixture, useFixtures } from './fixtures'

export type ApiError = {
    status: number
    code: string
    message: string
    details?: unknown
}

export class FrappeError extends Error implements ApiError {
    status: number
    code: string
    details?: unknown

    constructor({ status, code, message, details }: ApiError) {
        super(message)
        this.name = 'FrappeError'
        this.status = status
        this.code = code
        this.details = details
    }
}

const TIMEOUT_MS = 20000
const TOKEN_KEY = 'jurihr:token'

export const baseUrl = (import.meta.env.VITE_FRAPPE_BASE_URL || '').replace(/\/$/, '')

export function getToken(): string | null {
    return localStorage.getItem(TOKEN_KEY)
}

export function setToken(token: string | null) {
    if (token) localStorage.setItem(TOKEN_KEY, token)
    else localStorage.removeItem(TOKEN_KEY)
}

export type RequestOptions = {
    method?: string
    body?: unknown
    headers?: Record<string, string>
    signal?: AbortSignal
    raw?: boolean
}

let unauthorizedHandler: (() => void) | null = null

/** Registered by the auth store so a rejected token clears the session once, centrally. */
export function onUnauthorized(handler: () => void) {
    unauthorizedHandler = handler
}

export async function request<T>(path: string, options: RequestOptions = {}): Promise<T> {
    if (useFixtures()) return resolveFixture<T>(path, options)

    const { method = 'GET', body, headers = {}, signal } = options

    const requestHeaders: Record<string, string> = { Accept: 'application/json', ...headers }
    const token = getToken()
    if (token) requestHeaders.Authorization = `token ${token}`

    const isFormData = body instanceof FormData
    if (body !== undefined && !isFormData) requestHeaders['Content-Type'] = 'application/json'

    const controller = new AbortController()
    const timeout = setTimeout(() => controller.abort(), TIMEOUT_MS)
    signal?.addEventListener('abort', () => controller.abort(), { once: true })

    let response: Response
    try {
        response = await fetch(`${baseUrl}${path}`, {
            method,
            headers: requestHeaders,
            body: isFormData ? body : body === undefined ? undefined : JSON.stringify(body),
            credentials: 'include',
            signal: controller.signal,
        })
    } catch (error) {
        const aborted = controller.signal.aborted
        throw new FrappeError({
            status: 0,
            code: aborted ? 'Timeout' : 'NetworkError',
            message: aborted ? 'Permintaan melebihi batas waktu.' : httpMessage(0),
            details: error,
        })
    } finally {
        clearTimeout(timeout)
    }

    if (!response.ok) {
        const payload = await readBody(response)
        const error = normalizeError(response.status, payload)
        if (response.status === 401) unauthorizedHandler?.()
        throw error
    }

    if (options.raw) return response as unknown as T
    return (await readBody(response)) as T
}

async function readBody(response: Response): Promise<unknown> {
    const text = await response.text()
    if (!text) return null
    try {
        return JSON.parse(text)
    } catch {
        return { message: text }
    }
}

function parseServerMessages(raw: unknown): string | null {
    if (typeof raw !== 'string') return null
    try {
        const messages = JSON.parse(raw) as unknown[]
        const first = messages[0]
        if (typeof first !== 'string') return null
        try {
            const parsed = JSON.parse(first) as { message?: string }
            return parsed.message ?? first
        } catch {
            return first
        }
    } catch {
        return null
    }
}

function normalizeError(status: number, payload: unknown): FrappeError {
    const data = (payload ?? {}) as Record<string, unknown>
    const serverMessage = parseServerMessages(data._server_messages)
    const code =
        typeof data.exc_type === 'string' && data.exc_type ? data.exc_type : httpCode(status)
    const message =
        serverMessage ||
        (typeof data.message === 'string' && data.message ? data.message : '') ||
        httpMessage(status)

    return new FrappeError({ status, code, message, details: payload })
}

function httpCode(status: number): string {
    if (status === 401) return 'AuthenticationError'
    if (status === 403) return 'PermissionError'
    if (status === 404) return 'NotFound'
    if (status === 417) return 'ValidationError'
    if (status === 0) return 'NetworkError'
    return `HttpError${status}`
}

function httpMessage(status: number): string {
    if (status === 401) return 'Sesi berakhir. Silakan masuk kembali.'
    if (status === 403) return 'Anda tidak punya akses untuk tindakan ini.'
    if (status === 404) return 'Data tidak ditemukan.'
    if (status === 0) return 'Tidak dapat menghubungi server.'
    return 'Terjadi kesalahan pada server.'
}
