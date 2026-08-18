export type NavItem = {
    to: string
    label: string
    /** Single-path SVG icon (24x24, stroke-based) to avoid an icon dependency. */
    icon: string
}

export const ADMIN_NAV: NavItem[] = [
    { to: '/admin', label: 'Dashboard', icon: 'M3 12h7V3H3v9Zm11 9h7V3h-7v18ZM3 21h7v-6H3v6Z' },
    {
        to: '/admin/karyawan',
        label: 'Karyawan',
        icon: 'M16 19v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 8v-1a4 4 0 0 0-3-3.87M16 3.13A4 4 0 0 1 16 11',
    },
    {
        to: '/admin/absensi',
        label: 'Absensi',
        icon: 'M9 11l3 3 5-5M4 5h16v16H4V5Zm4-3v4m8-4v4',
    },
    {
        to: '/admin/jadwal',
        label: 'Jadwal',
        icon: 'M12 8v4l3 2m-3 8a10 10 0 1 1 0-20 10 10 0 0 1 0 20Z',
    },
    {
        to: '/admin/pengajuan',
        label: 'Pengajuan',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6l6 6v10a2 2 0 0 1-2 2Z',
    },
    {
        to: '/admin/slip-gaji',
        label: 'Slip Gaji',
        icon: 'M12 6v12m3-9H9.5a2.5 2.5 0 0 0 0 5h5a2.5 2.5 0 0 1 0 5H8',
    },
    {
        to: '/admin/pengumuman',
        label: 'Pengumuman',
        icon: 'M3 11v2a1 1 0 0 0 1 1h3l5 4V6L7 10H4a1 1 0 0 0-1 1Zm14-3a5 5 0 0 1 0 8',
    },
    {
        to: '/admin/aktivitas',
        label: 'Aktivitas',
        icon: 'M3 12h4l3 8 4-16 3 8h4',
    },
    {
        to: '/admin/pengaturan',
        label: 'Pengaturan',
        icon: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7.4-3a7.4 7.4 0 0 0-.1-1.2l2-1.6-2-3.4-2.4 1a7.5 7.5 0 0 0-2-1.2L14.5 2h-5l-.4 2.6c-.7.3-1.4.7-2 1.2l-2.4-1-2 3.4 2 1.6a7.5 7.5 0 0 0 0 2.4l-2 1.6 2 3.4 2.4-1c.6.5 1.3.9 2 1.2l.4 2.6h5l.4-2.6c.7-.3 1.4-.7 2-1.2l2.4 1 2-3.4-2-1.6c.1-.4.1-.8.1-1.2Z',
    },
]

export const USER_NAV: NavItem[] = [
    { to: '/', label: 'Home', icon: 'M3 10.5 12 3l9 7.5V21H3V10.5Zm6 10.5v-7h6v7' },
    {
        to: '/kalender',
        label: 'Kalender',
        icon: 'M4 5h16v16H4V5Zm4-3v4m8-4v4M4 10h16',
    },
    {
        to: '/pengajuan',
        label: 'Pengajuan',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6l6 6v10a2 2 0 0 1-2 2Z',
    },
    {
        to: '/slip-gaji',
        label: 'Slip Gaji',
        icon: 'M12 6v12m3-9H9.5a2.5 2.5 0 0 0 0 5h5a2.5 2.5 0 0 1 0 5H8',
    },
    {
        to: '/profil',
        label: 'Profil',
        icon: 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z',
    },
]
