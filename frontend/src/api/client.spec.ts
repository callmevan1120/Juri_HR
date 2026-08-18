import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import type { ApiError } from './client'

const ORIGINAL_ENV = { ...import.meta.env }

async function loadClient(env: Record<string, string> = {}) {
  vi.resetModules()
  vi.stubEnv('VITE_USE_FIXTURES', env.VITE_USE_FIXTURES ?? 'false')
  vi.stubEnv('VITE_FRAPPE_BASE_URL', env.VITE_FRAPPE_BASE_URL ?? 'http://localhost:8000')
  return import('./client')
}

function jsonResponse(body: unknown, status = 200) {
  return new Response(JSON.stringify(body), { status })
}

async function rejection(promise: Promise<unknown>): Promise<ApiError> {
  return promise.then(
    () => {
      throw new Error('expected the request to reject')
    },
    (error: ApiError) => error,
  )
}

describe('api client', () => {
  beforeEach(() => {
    localStorage.clear()
  })

  afterEach(() => {
    vi.unstubAllEnvs()
    vi.unstubAllGlobals()
    vi.restoreAllMocks()
    Object.assign(import.meta.env, ORIGINAL_ENV)
  })

  it('injects the stored token as an Authorization header', async () => {
    const { request, setToken } = await loadClient()
    setToken('key:secret')
    const fetchMock = vi.fn().mockResolvedValue(jsonResponse({ data: [] }))
    vi.stubGlobal('fetch', fetchMock)

    await request('/api/resource/Employee')

    const headers = fetchMock.mock.calls[0][1].headers as Record<string, string>
    expect(headers.Authorization).toBe('token key:secret')
  })

  it('omits the Authorization header when there is no token', async () => {
    const { request } = await loadClient()
    const fetchMock = vi.fn().mockResolvedValue(jsonResponse({ data: [] }))
    vi.stubGlobal('fetch', fetchMock)

    await request('/api/resource/Employee')

    const headers = fetchMock.mock.calls[0][1].headers as Record<string, string>
    expect(headers.Authorization).toBeUndefined()
  })

  it('normalizes a Frappe error into { status, code, message }', async () => {
    const { request, FrappeError } = await loadClient()
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue(
        jsonResponse(
          {
            exc_type: 'ValidationError',
            _server_messages: JSON.stringify([JSON.stringify({ message: 'Di luar radius' })]),
          },
          417,
        ),
      ),
    )

    const error = await rejection(
      request('/api/method/juri_hr.attendance.check_in', { method: 'POST' }),
    )

    expect(error).toBeInstanceOf(FrappeError)
    expect(error.status).toBe(417)
    expect(error.code).toBe('ValidationError')
    expect(error.message).toBe('Di luar radius')
  })

  it('clears the session and notifies the handler on 401', async () => {
    const { request, setToken, getToken, onUnauthorized } = await loadClient()
    setToken('key:secret')
    const onUnauth = vi.fn(() => setToken(null))
    onUnauthorized(onUnauth)
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue(jsonResponse({ message: 'Not permitted' }, 401)))

    await request('/api/method/juri_hr.auth.session').catch(() => undefined)

    expect(onUnauth).toHaveBeenCalledOnce()
    expect(getToken()).toBeNull()
  })

  it('reports a network failure as status 0', async () => {
    const { request } = await loadClient()
    vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new TypeError('failed to fetch')))

    const error = await rejection(request('/api/resource/Employee'))

    expect(error.status).toBe(0)
    expect(error.code).toBe('NetworkError')
  })

  it('serves fixtures without touching the network when fixtures are enabled', async () => {
    const { request } = await loadClient({ VITE_USE_FIXTURES: 'true' })
    const fetchMock = vi.fn()
    vi.stubGlobal('fetch', fetchMock)

    const result = await request<{ data: { employee_name: string }[] }>(
      '/api/resource/Employee?limit_page_length=2',
    )

    expect(fetchMock).not.toHaveBeenCalled()
    expect(result.data).toHaveLength(2)
  })

  it('filters and paginates fixture lists', async () => {
    const { request } = await loadClient({ VITE_USE_FIXTURES: 'true' })
    const filters = encodeURIComponent(JSON.stringify([['custom_work_pattern', '=', 'shift']]))

    const result = await request<{ data: { employee_name: string }[] }>(
      `/api/resource/Employee?filters=${filters}&limit_start=1&limit_page_length=1`,
    )

    expect(result.data).toHaveLength(1)
    expect(result.data[0].employee_name).toBe('Andi Wijaya')
  })
})
