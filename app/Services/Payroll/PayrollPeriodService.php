<?php

namespace App\Services\Payroll;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

class PayrollPeriodService
{
    /**
     * @return array{period_type: string, period_start: string, period_end: string, period_sequence: int, work_days: int}
     */
    public function resolve(string $type, int $year, int $month, ?int $sequence = null): array
    {
        $type = strtolower($type);

        if ($year < 2000 || $year > 2200) {
            throw new InvalidArgumentException("Unsupported payroll period year [{$year}].");
        }

        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException("Unsupported payroll period month [{$month}].");
        }

        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->startOfDay();

        return match ($type) {
            'monthly' => $this->monthly($monthStart, $monthEnd),
            'weekly' => $this->weekly($monthStart, $monthEnd, $sequence ?? 1),
            'daily' => $this->daily($monthStart, $monthEnd, $sequence ?? (int) $monthStart->day),
            default => throw new InvalidArgumentException("Unsupported payroll period type [{$type}]."),
        };
    }

    /**
     * @return array{period_type: string, period_start: string, period_end: string, period_sequence: int, work_days: int}
     */
    private function monthly(Carbon $monthStart, Carbon $monthEnd): array
    {
        return $this->payload('monthly', $monthStart, $monthEnd, 1);
    }

    /**
     * @return array{period_type: string, period_start: string, period_end: string, period_sequence: int, work_days: int}
     */
    private function weekly(Carbon $monthStart, Carbon $monthEnd, int $sequence): array
    {
        $sequence = max(1, min(6, $sequence));
        $start = $monthStart->copy()->addDays(($sequence - 1) * 7);

        if ($start->gt($monthEnd)) {
            $start = $monthEnd->copy();
        }

        return $this->payload('weekly', $start, $start->copy()->addDays(6)->min($monthEnd), $sequence);
    }

    /**
     * @return array{period_type: string, period_start: string, period_end: string, period_sequence: int, work_days: int}
     */
    private function daily(Carbon $monthStart, Carbon $monthEnd, int $sequence): array
    {
        $sequence = max(1, min((int) $monthEnd->day, $sequence));
        $date = $monthStart->copy()->day($sequence);

        return $this->payload('daily', $date, $date, $sequence);
    }

    /**
     * @return array{period_type: string, period_start: string, period_end: string, period_sequence: int, work_days: int}
     */
    private function payload(string $type, Carbon $start, Carbon $end, int $sequence): array
    {
        return [
            'period_type' => $type,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'period_sequence' => $sequence,
            'work_days' => (int) $start->diffInDays($end) + 1,
        ];
    }
}
