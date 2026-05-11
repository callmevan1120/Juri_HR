<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntegrationEndpoint extends Model
{
    public const PROVIDER_CUSTOM = 'custom';

    public const PROVIDER_ACCOUNTING = 'accounting';

    public const PROVIDER_PAYROLL = 'payroll';

    public const PROVIDER_SLACK = 'slack';

    public const PROVIDER_TELEGRAM = 'telegram';

    public const PROVIDER_WHATSAPP = 'whatsapp';

    public const PROVIDER_GOOGLE_CALENDAR = 'google_calendar';

    public const PROVIDER_SSO = 'sso';

    protected $fillable = [
        'name',
        'provider',
        'event_keys',
        'url',
        'secret',
        'headers',
        'is_active',
        'last_success_at',
        'last_failure_at',
        'last_error',
        'failure_count',
    ];

    protected $hidden = [
        'secret',
    ];

    protected $casts = [
        'event_keys' => 'array',
        'headers' => 'array',
        'secret' => 'encrypted',
        'is_active' => 'boolean',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(IntegrationDelivery::class);
    }
}
