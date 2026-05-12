<?php

namespace App\Livewire\Admin;

use App\Livewire\Traits\AttendanceDetailTrait;
use App\Queries\Attendance\AdminAttendanceGridQuery;
use Illuminate\Support\Carbon;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceComponent extends Component
{
    use AttendanceDetailTrait;
    use InteractsWithBanner, WithPagination;

    // filter
    public $startDate;

    public $endDate;

    public ?string $division = null;

    public ?string $jobTitle = null;

    public ?string $search = null;

    public string $riskFilter = 'all';

    public function mount()
    {
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updating($key): void
    {
        if (in_array($key, ['search', 'division', 'jobTitle', 'startDate', 'endDate', 'riskFilter'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        // Validation: Prevent inverted range
        if ($start->gt($end)) {
            $temp = $start;
            $start = $end;
            $end = $temp;
        }

        $dates = $start->range($end)->toArray();
        $employees = app(AdminAttendanceGridQuery::class)->employees(
            auth()->user(),
            $start,
            $end,
            $this->division,
            $this->jobTitle,
            $this->search,
            $this->riskFilter,
        );

        return view('livewire.admin.attendance', [
            'employees' => $employees,
            'dates' => $dates,
            'recentReportRuns' => app(\App\Support\ImportExportRunViewService::class)
                ->recentForResources(['attendance_report'], auth()->user(), 4),
        ]);
    }
}
