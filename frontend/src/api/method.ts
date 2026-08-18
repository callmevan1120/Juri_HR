import { request } from './client'

export function callMethod<T>(name: string, params?: Record<string, unknown>): Promise<T> {
  if (!params) return request<{ message: T }>(`/api/method/${name}`).then((res) => res.message)
  return request<{ message: T }>(`/api/method/${name}`, { method: 'POST', body: params }).then(
    (res) => res.message,
  )
}
