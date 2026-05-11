<?php

namespace App\Models;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class ActivityLogDetail extends Model
{
    protected $fillable = [
        'activity_log_id',
        'entity_type',
        'entity_id',
        'field',
        'old_value',
        'new_value',
        'metadata',
    ];

    protected $hidden = ['integrity_hash'];

    protected function casts(): array
    {
        return [
            'old_value' => 'array',
            'new_value' => 'array',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ActivityLogDetail $detail): void {
            $detail->integrity_hash = $detail->makeIntegrityHash();
        });

        static::updating(function (): void {
            throw new AuthorizationException('Activity log details are append-only and cannot be modified.');
        });

        static::deleting(function (): void {
            throw new AuthorizationException('Activity log details are append-only and cannot be deleted.');
        });
    }

    public function activityLog()
    {
        return $this->belongsTo(ActivityLog::class);
    }

    public function hasValidIntegrityHash(): bool
    {
        return is_string($this->integrity_hash)
            && hash_equals($this->integrity_hash, $this->makeIntegrityHash());
    }

    public function makeIntegrityHash(): string
    {
        return hash_hmac('sha256', $this->integrityPayload(), (string) Config::get('app.key'));
    }

    protected function integrityPayload(): string
    {
        return json_encode([
            'activity_log_id' => $this->activity_log_id,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'field' => $this->field,
            'old_value' => $this->old_value,
            'new_value' => $this->new_value,
            'metadata' => $this->metadata,
        ], JSON_THROW_ON_ERROR);
    }
}
