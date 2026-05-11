<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceOfflineSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'client_uuid',
        'processed_attendance_id',
        'status',
        'action',
        'barcode_data',
        'latitude',
        'longitude',
        'accuracy',
        'gps_variance',
        'captured_at',
        'synced_at',
        'photo_path',
        'risk_score',
        'risk_level',
        'payload',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'accuracy' => 'float',
            'gps_variance' => 'float',
            'captured_at' => 'datetime',
            'synced_at' => 'datetime',
            'risk_score' => 'integer',
            'payload' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processedAttendance()
    {
        return $this->belongsTo(Attendance::class, 'processed_attendance_id');
    }
}
