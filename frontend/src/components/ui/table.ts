export type Column<T> = {
    key: keyof T & string
    label: string
    sortable?: boolean
    align?: 'left' | 'right'
}
