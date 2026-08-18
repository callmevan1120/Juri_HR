import { describe, expect, it } from 'vitest'
import {
  formatCurrency,
  formatDate,
  formatMinutes,
  formatTime,
} from './format'

describe('formatters', () => {
  it('formats rupiah without decimals', () => {
    expect(formatCurrency(4500000).replace(/\u00a0/g, ' ')).toBe('Rp 4.500.000')
  })

  it('returns a dash for missing values', () => {
    expect(formatCurrency(null)).toBe('-')
    expect(formatDate(undefined)).toBe('-')
    expect(formatTime('')).toBe('-')
    expect(formatMinutes(null)).toBe('-')
  })

  it('formats Frappe dates in Indonesian', () => {
    expect(formatDate('2026-08-18')).toBe('18 Agustus 2026')
  })

  it('trims seconds from Frappe times', () => {
    expect(formatTime('07:30:00')).toBe('07:30')
    expect(formatTime('2026-08-18 16:45:12')).toBe('16:45')
  })

  it('formats durations as hours and minutes', () => {
    expect(formatMinutes(45)).toBe('45m')
    expect(formatMinutes(125)).toBe('2j 5m')
    expect(formatMinutes(-15)).toBe('-15m')
  })
})
