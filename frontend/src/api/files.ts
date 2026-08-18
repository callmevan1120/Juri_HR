import { baseUrl, getToken, request } from './client'

export type UploadedFile = {
    name: string
    file_url: string
    file_name: string
    is_private: 0 | 1
}

export function uploadPrivateFile(
    file: File | Blob,
    { doctype, docname, fileName }: { doctype?: string; docname?: string; fileName?: string } = {},
): Promise<UploadedFile> {
    const form = new FormData()
    form.append('file', file, fileName ?? (file instanceof File ? file.name : 'upload'))
    form.append('is_private', '1')
    if (doctype) form.append('doctype', doctype)
    if (docname) form.append('docname', docname)

    return request<{ message: UploadedFile }>('/api/method/upload_file', {
        method: 'POST',
        body: form,
    }).then((res) => res.message)
}

/**
 * Private files must never be embedded by URL: fetch them with the auth header
 * and hand the caller a blob URL that the browser can render.
 */
export async function fetchPrivateBlob(fileUrl: string): Promise<Blob> {
    const token = getToken()
    const response = await fetch(`${baseUrl}${fileUrl}`, {
        headers: token ? { Authorization: `token ${token}` } : {},
        credentials: 'include',
    })
    if (!response.ok) throw new Error(`Gagal memuat lampiran (${response.status})`)
    return response.blob()
}
