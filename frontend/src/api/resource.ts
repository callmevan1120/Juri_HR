import { request } from './client'

export type ListParams = {
  fields?: string[]
  filters?: unknown[]
  limit_start?: number
  limit_page_length?: number
  order_by?: string
}

function toQuery(params: ListParams): string {
  const search = new URLSearchParams()
  if (params.fields) search.set('fields', JSON.stringify(params.fields))
  if (params.filters) search.set('filters', JSON.stringify(params.filters))
  if (params.limit_start !== undefined) search.set('limit_start', String(params.limit_start))
  if (params.limit_page_length !== undefined)
    search.set('limit_page_length', String(params.limit_page_length))
  if (params.order_by) search.set('order_by', params.order_by)
  const query = search.toString()
  return query ? `?${query}` : ''
}

export function listResource<T>(doctype: string, params: ListParams = {}): Promise<T[]> {
  return request<{ data: T[] }>(
    `/api/resource/${encodeURIComponent(doctype)}${toQuery(params)}`,
  ).then((res) => res.data)
}

export function getResource<T>(doctype: string, name: string): Promise<T> {
  return request<{ data: T }>(
    `/api/resource/${encodeURIComponent(doctype)}/${encodeURIComponent(name)}`,
  ).then((res) => res.data)
}

export function createResource<T>(doctype: string, doc: Record<string, unknown>): Promise<T> {
  return request<{ data: T }>(`/api/resource/${encodeURIComponent(doctype)}`, {
    method: 'POST',
    body: doc,
  }).then((res) => res.data)
}

export function updateResource<T>(
  doctype: string,
  name: string,
  doc: Record<string, unknown>,
): Promise<T> {
  return request<{ data: T }>(
    `/api/resource/${encodeURIComponent(doctype)}/${encodeURIComponent(name)}`,
    { method: 'PUT', body: doc },
  ).then((res) => res.data)
}

export function deleteResource(doctype: string, name: string): Promise<unknown> {
  return request(`/api/resource/${encodeURIComponent(doctype)}/${encodeURIComponent(name)}`, {
    method: 'DELETE',
  })
}
