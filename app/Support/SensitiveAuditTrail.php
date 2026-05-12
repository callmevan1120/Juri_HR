<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\ActivityLogDetail;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\CashAdvance;
use App\Models\CompanyAsset;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use App\Models\Reimbursement;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SystemBackupRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SensitiveAuditTrail
{
    /**
     * @return array<class-string<Model>, list<string>>
     */
    public static function watchedFields(): array
    {
        return [
            User::class => [
                'basic_salary',
                'hourly_rate',
                'ptkp_status',
                'group',
                'employment_status',
                'probation_ends_at',
                'contract_ends_at',
                'resigned_at',
                'account_auto_disable_at',
                'payslip_password',
                'payslip_password_set_at',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
            ],
            Payroll::class => [
                'type',
                'basic_salary',
                'allowances',
                'deductions',
                'overtime_pay',
                'total_allowance',
                'total_deduction',
                'net_salary',
                'details',
                'status',
                'paid_at',
            ],
            PayrollComponent::class => [
                'name',
                'type',
                'calculation_type',
                'amount',
                'percentage',
                'is_taxable',
                'is_active',
            ],
            Role::class => [
                'name',
                'slug',
                'description',
                'permissions',
                'is_super_admin',
            ],
            AttendanceCorrection::class => [
                'request_type',
                'requested_time_in',
                'requested_time_out',
                'requested_shift_id',
                'current_snapshot',
                'reason',
                'status',
                'head_approved_by',
                'head_approved_at',
                'reviewed_by',
                'reviewed_at',
                'rejection_note',
            ],
            Attendance::class => [
                'status',
                'leave_type_id',
                'approval_status',
                'approved_by',
                'approved_at',
                'rejection_note',
            ],
            Reimbursement::class => [
                'amount',
                'status',
                'approval_matrix_rule_id',
                'approval_current_step',
                'admin_note',
                'approved_by',
                'head_approved_by',
                'head_approved_at',
                'finance_approved_by',
                'finance_approved_at',
            ],
            CashAdvance::class => [
                'amount',
                'status',
                'approval_matrix_rule_id',
                'approval_current_step',
                'approved_by',
                'approved_at',
                'head_approved_by',
                'head_approved_at',
                'finance_approved_by',
                'finance_approved_at',
            ],
            CompanyAsset::class => [
                'user_id',
                'date_assigned',
                'return_date',
                'status',
                'notes',
            ],
            Setting::class => [
                'key',
                'value',
                'group',
                'type',
            ],
            SystemBackupRun::class => [
                'type',
                'status',
                'file_path',
                'file_name',
                'size_bytes',
                'error_message',
                'started_at',
                'completed_at',
                'failed_at',
            ],
        ];
    }

    public function recordModelUpdate(Model $model): void
    {
        if (! Schema::hasTable('activity_log_details')) {
            return;
        }

        $fields = $this->watchedFieldsFor($model);
        $changedFields = array_values(array_filter(
            $fields,
            fn (string $field): bool => $model->wasChanged($field),
        ));

        if ($changedFields === []) {
            return;
        }

        $activityLog = ActivityLog::record(
            'Sensitive Field Changed',
            sprintf(
                '%s #%s changed fields: %s',
                class_basename($model),
                (string) $model->getKey(),
                implode(', ', $changedFields),
            ),
        );

        if (! $activityLog instanceof ActivityLog) {
            return;
        }

        foreach ($changedFields as $field) {
            try {
                ActivityLogDetail::create([
                    'activity_log_id' => $activityLog->id,
                    'entity_type' => $model::class,
                    'entity_id' => (string) $model->getKey(),
                    'field' => $field,
                    'old_value' => $this->normalizeValue($field, $model->getOriginal($field), $model),
                    'new_value' => $this->normalizeValue($field, $model->getAttribute($field), $model),
                    'metadata' => [
                        'table' => $model->getTable(),
                        'model' => class_basename($model),
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::warning('Sensitive audit trail detail failed without blocking the request.', [
                    'model' => $model::class,
                    'id' => (string) $model->getKey(),
                    'field' => $field,
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function watchedFieldsFor(Model $model): array
    {
        foreach (self::watchedFields() as $class => $fields) {
            if ($model instanceof $class) {
                return $fields;
            }
        }

        return [];
    }

    /**
     * @return array{redacted?: bool, masked?: bool, value?: mixed}
     */
    private function normalizeValue(string $field, mixed $value, ?Model $model = null): array
    {
        if ($model instanceof Setting && $field === 'value' && str((string) $model->key)->contains(['license', 'secret', 'token', 'password'])) {
            return ['redacted' => true];
        }

        if ($this->shouldRedact($field)) {
            return ['redacted' => true];
        }

        if ($this->shouldMask($field)) {
            return [
                'masked' => true,
                'value' => $this->maskScalar($value),
            ];
        }

        if ($value instanceof Carbon) {
            return ['value' => $value->toIso8601String()];
        }

        if ($value instanceof \DateTimeInterface) {
            return ['value' => Carbon::instance($value)->toIso8601String()];
        }

        return ['value' => $value];
    }

    private function shouldRedact(string $field): bool
    {
        return str($field)->contains(['password', 'token', 'secret', 'recovery', 'enterprise_license']);
    }

    private function shouldMask(string $field): bool
    {
        return str($field)->contains(['bank', 'rekening', 'account_number']);
    }

    private function maskScalar(mixed $value): mixed
    {
        if (! is_scalar($value) && $value !== null) {
            return '[masked]';
        }

        $text = (string) $value;

        if ($text === '') {
            return $text;
        }

        $lastFour = substr($text, -4);

        return str_repeat('*', max(strlen($text) - 4, 0)).$lastFour;
    }
}
