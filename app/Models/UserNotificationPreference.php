<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    public const CHANNEL_IN_APP = 'in_app';

    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_WHATSAPP = 'whatsapp';

    public const CHANNEL_TELEGRAM = 'telegram';

    public const CHANNEL_WEBHOOK = 'webhook';

    public const CHANNEL_DIGEST = 'digest';

    protected $fillable = [
        'user_id',
        'event_key',
        'channels',
        'digest_enabled',
        'digest_frequency',
        'external_routes',
    ];

    protected $casts = [
        'channels' => 'array',
        'digest_enabled' => 'boolean',
        'external_routes' => 'array',
    ];

    /**
     * @return array<int, string>
     */
    public static function allowedChannels(): array
    {
        return [
            self::CHANNEL_IN_APP,
            self::CHANNEL_EMAIL,
            self::CHANNEL_WHATSAPP,
            self::CHANNEL_TELEGRAM,
            self::CHANNEL_WEBHOOK,
            self::CHANNEL_DIGEST,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
