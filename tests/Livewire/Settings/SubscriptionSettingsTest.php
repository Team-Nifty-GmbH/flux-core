<?php

use FluxErp\Livewire\Settings\SubscriptionSettings;
use FluxErp\Settings\SubscriptionSettings as SubscriptionSettingsModel;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SubscriptionSettings::class)
        ->assertOk();
});

test('loads settings on mount', function (): void {
    SubscriptionSettingsModel::fake([
        'cancellation_text' => '<p>Test cancellation text</p>',
        'default_cancellation_notice_value' => 3,
        'default_cancellation_notice_unit' => 'months',
    ]);

    Livewire::test(SubscriptionSettings::class)
        ->assertSet('subscriptionSettingsForm.cancellation_text', '<p>Test cancellation text</p>')
        ->assertSet('subscriptionSettingsForm.default_cancellation_notice_value', 3)
        ->assertSet('subscriptionSettingsForm.default_cancellation_notice_unit', 'months');
});

test('can save settings', function (): void {
    SubscriptionSettingsModel::fake([
        'cancellation_text' => null,
        'default_cancellation_notice_value' => 0,
        'default_cancellation_notice_unit' => 'days',
    ]);

    Livewire::test(SubscriptionSettings::class)
        ->set('subscriptionSettingsForm.cancellation_text', '<p>New cancellation text</p>')
        ->set('subscriptionSettingsForm.default_cancellation_notice_value', 2)
        ->set('subscriptionSettingsForm.default_cancellation_notice_unit', 'weeks')
        ->call('save')
        ->assertReturned(true);
});
