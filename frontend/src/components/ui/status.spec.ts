import { describe, expect, it } from 'vitest'
import { BADGE_TONE_CLASSES, DAILY_STATUSES, statusLabel, statusTone } from './status'

describe('status map', () => {
  it('maps every daily status to a known tone', () => {
    for (const status of DAILY_STATUSES) {
      expect(BADGE_TONE_CLASSES[statusTone(status)]).toBeTruthy()
    }
  })

  it('falls back to gray for unknown statuses', () => {
    expect(statusTone('whatever')).toBe('gray')
    expect(statusLabel('whatever')).toBe('whatever')
  })

  it('labels statuses in Indonesian', () => {
    expect(statusLabel('late')).toBe('Terlambat')
    expect(statusLabel('rejected')).toBe('Ditolak')
  })
})
