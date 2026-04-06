<?php

use FluxErp\Actions\Setting\UpdateSetting;
use FluxErp\Settings\CoreSettings;

test('update setting', function (): void {
    $result = UpdateSetting::make([
        'settings_class' => CoreSettings::class,
        'formal_salutation' => true,
    ])->validate()->execute();

    expect($result)->toBeInstanceOf(FluxErp\Settings\FluxSettings::class);
});

test('update setting requires settings_class', function (): void {
    UpdateSetting::assertValidationErrors([], 'settings_class');
});
