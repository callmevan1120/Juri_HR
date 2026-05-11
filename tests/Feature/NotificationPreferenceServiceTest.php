<?php

use App\Models\User;
use App\Models\UserNotificationPreference;
use App\Support\NotificationPreferenceService;

test('users can configure notification channels and external routes per event', function () {
    $user = User::factory()->create();
    $service = app(NotificationPreferenceService::class);

    $preference = $service->setPreference(
        $user,
        'reimbursement.requested',
        [
            UserNotificationPreference::CHANNEL_IN_APP,
            UserNotificationPreference::CHANNEL_EMAIL,
            UserNotificationPreference::CHANNEL_TELEGRAM,
            UserNotificationPreference::CHANNEL_WEBHOOK,
        ],
        digestEnabled: true,
        digestFrequency: 'daily',
        externalRoutes: [
            UserNotificationPreference::CHANNEL_TELEGRAM => '@hr_approvals',
            UserNotificationPreference::CHANNEL_WEBHOOK => 'https://example.test/hooks/hr',
        ],
    );

    expect($preference->channels)->toBe([
        UserNotificationPreference::CHANNEL_IN_APP,
        UserNotificationPreference::CHANNEL_EMAIL,
        UserNotificationPreference::CHANNEL_TELEGRAM,
        UserNotificationPreference::CHANNEL_WEBHOOK,
    ])
        ->and($service->laravelChannelsFor($user, 'reimbursement.requested'))->toBe(['database', 'mail'])
        ->and($service->prefers($user, 'reimbursement.requested', UserNotificationPreference::CHANNEL_TELEGRAM))->toBeTrue()
        ->and($service->externalRoutesFor($user, 'reimbursement.requested'))->toBe([
            UserNotificationPreference::CHANNEL_TELEGRAM => '@hr_approvals',
            UserNotificationPreference::CHANNEL_WEBHOOK => 'https://example.test/hooks/hr',
        ])
        ->and($service->digestRecipients('reimbursement.requested', 'daily'))->toHaveCount(1);
});

test('notification preferences fall back to in app channel', function () {
    $user = User::factory()->create();
    $service = app(NotificationPreferenceService::class);

    expect($service->channelsFor($user, 'attendance.risk'))->toBe([UserNotificationPreference::CHANNEL_IN_APP])
        ->and($service->laravelChannelsFor($user, 'attendance.risk'))->toBe(['database']);
});
