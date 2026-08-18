import type { RequestOptions } from './client'
import { baseUrl, FrappeError } from './client'

const fixtures = import.meta.glob<{ default: unknown }>('./fixtures/*.json')

export function useFixtures(): boolean {
    return import.meta.env.VITE_USE_FIXTURES === 'true' || baseUrl === ''
}

function slug(name: string): string {
    return decodeURIComponent(name).trim().toLowerCase().replace(/\s+/g, '-')
}

async function loadFixture(name: string): Promise<unknown> {
    const loader = fixtures[`./fixtures/${name}.json`]
    if (!loader) {
        throw new FrappeError({
            status: 404,
            code: 'FixtureNotFound',
            message: `Fixture "${name}" tidak tersedia.`,
        })
    }
    return (await loader()).default
}

type Filter = [string, string, unknown]

function matchesFilters(row: Record<string, unknown>, filters: Filter[]): boolean {
    return filters.every(([field, operator, value]) => {
        const actual = row[field]
        switch (operator) {
            case '=':
            case '==':
                return actual === value
            case '!=':
                return actual !== value
            case '>':
                return (actual as number) > (value as number)
            case '>=':
                return (actual as number) >= (value as number)
            case '<':
                return (actual as number) < (value as number)
            case '<=':
                return (actual as number) <= (value as number)
            case 'in':
                return Array.isArray(value) && value.includes(actual)
            case 'like':
                return String(actual ?? '')
                    .toLowerCase()
                    .includes(String(value).replace(/%/g, '').toLowerCase())
            default:
                return true
        }
    })
}

function parseFilters(raw: string | null): Filter[] {
    if (!raw) return []
    try {
        const parsed = JSON.parse(raw)
        return Array.isArray(parsed) ? (parsed as Filter[]) : []
    } catch {
        return []
    }
}

const FIXTURE_USER_KEY = 'jurihr:fixture-user'

/**
 * Fixture mode accepts any password. The submitted email decides the role so both
 * roles are testable without editing fixture files: an address starting with
 * `hrd` gets the HRD roles, anything else is a plain employee.
 */
function fixtureRoles(email: string | null): string[] {
    const isHrd = (email ?? '').toLowerCase().startsWith('hrd')
    return isHrd ? ['HR Manager', 'Employee'] : ['Employee']
}

function withFixtureRoles(payload: unknown, email: string | null): unknown {
    const data = payload as { message?: Record<string, unknown> }
    if (!data?.message) return payload
    return {
        message: {
            ...data.message,
            user: email ?? data.message.user,
            full_name: fixtureRoles(email).includes('HR Manager') ? 'HRD Demo' : 'Karyawan Demo',
            roles: fixtureRoles(email),
        },
    }
}

export async function resolveFixture<T>(path: string, options: RequestOptions): Promise<T> {
    const [pathname, search = ''] = path.split('?')
    const params = new URLSearchParams(search)
    const method = (options.method ?? 'GET').toUpperCase()

    const resourceMatch = pathname.match(/^\/api\/resource\/([^/]+)(?:\/(.+))?$/)
    if (resourceMatch) {
        const [, doctype, docname] = resourceMatch
        const data = (await loadFixture(slug(doctype))) as Record<string, unknown>[]

        if (docname) {
            const found = data.find((row) => row.name === decodeURIComponent(docname))
            if (!found) {
                throw new FrappeError({
                    status: 404,
                    code: 'NotFound',
                    message: 'Data tidak ditemukan.',
                })
            }
            return { data: found } as T
        }

        if (method !== 'GET') return { data: (options.body ?? {}) as Record<string, unknown> } as T

        const filtered = data.filter((row) =>
            matchesFilters(row, parseFilters(params.get('filters'))),
        )
        const start = Number(params.get('limit_start') ?? 0)
        const pageLength = Number(params.get('limit_page_length') ?? 20)
        const page = pageLength === 0 ? filtered : filtered.slice(start, start + pageLength)
        return { data: page } as T
    }

    const methodMatch = pathname.match(/^\/api\/method\/(.+)$/)
    if (methodMatch) {
        const method = methodMatch[1]
        const body = (options.body ?? {}) as { usr?: string }

        if (method === 'login' && body.usr) localStorage.setItem(FIXTURE_USER_KEY, body.usr)
        if (method === 'logout') localStorage.removeItem(FIXTURE_USER_KEY)

        const payload = await loadFixture(slug(method))
        if (method === 'juri_hr.auth.issue_token' || method === 'juri_hr.auth.session') {
            return withFixtureRoles(payload, localStorage.getItem(FIXTURE_USER_KEY)) as T
        }
        return payload as T
    }

    throw new FrappeError({
        status: 404,
        code: 'FixtureNotFound',
        message: `Tidak ada fixture untuk ${path}.`,
    })
}
