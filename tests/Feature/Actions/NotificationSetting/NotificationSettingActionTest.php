<?php

use FluxErp\Actions\NotificationSetting\UpdateNotificationSetting;

test('update notification setting', function (): void {
    $result = UpdateNotificationSetting::make([
        'notification_type' => 'FluxErp\\Notifications\\CommentCreatedNotification',
        'channel' => 'database',
        'is_active' => true,
    ])->validate()->execute();

    expect($result)->not->toBeNull();
});

test('update notification setting requires notification_type and channel', function (): void {
    UpdateNotificationSetting::assertValidationErrors([], ['notification_type', 'channel']);
});
