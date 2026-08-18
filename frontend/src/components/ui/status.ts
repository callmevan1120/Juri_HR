export const DAILY_STATUSES = [
  'present',
  'late',
  'rejected',
  'absent',
  'izin',
  'cuti',
  'sakit',
  'off',
  'holiday',
] as const

export type DailyStatus = (typeof DAILY_STATUSES)[number]

export type BadgeTone =
  | 'green'
  | 'amber'
  | 'red'
  | 'red-muted'
  | 'blue'
  | 'cyan'
  | 'violet'
  | 'gray'

export const BADGE_TONE_CLASSES: Record<BadgeTone, string> = {
  green: 'bg-primary-100 text-primary-800 dark:bg-primary-900/40 dark:text-primary-200',
  amber: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
  red: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
  'red-muted': 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300',
  blue: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
  cyan: 'bg-brand-100 text-brand-800 dark:bg-brand-900/40 dark:text-brand-200',
  violet: 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-200',
  gray: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
}

const STATUS_TONES: Record<DailyStatus, BadgeTone> = {
  present: 'green',
  late: 'amber',
  rejected: 'red',
  absent: 'red-muted',
  izin: 'blue',
  cuti: 'cyan',
  sakit: 'violet',
  off: 'gray',
  holiday: 'gray',
}

const STATUS_LABELS: Record<DailyStatus, string> = {
  present: 'Hadir',
  late: 'Terlambat',
  rejected: 'Ditolak',
  absent: 'Tidak Hadir',
  izin: 'Izin',
  cuti: 'Cuti',
  sakit: 'Sakit',
  off: 'Libur',
  holiday: 'Hari Libur',
}

export function statusTone(status: string): BadgeTone {
  return STATUS_TONES[status as DailyStatus] ?? 'gray'
}

export function statusLabel(status: string): string {
  return STATUS_LABELS[status as DailyStatus] ?? status
}
