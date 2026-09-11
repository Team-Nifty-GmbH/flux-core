<?php

use FluxErp\Models\MailAccount;
use FluxErp\Policies\MailAccountPolicy;
use FluxErp\Tests\Fixtures\Models\InstanceMailAccount;
use FluxErp\Tests\Fixtures\Models\OverriddenMailAccount;
use FluxErp\Tests\Fixtures\Models\RecordWithoutPolicy;
use FluxErp\Tests\Fixtures\Policies\OverriddenMailAccountPolicy;
use FluxErp\Tests\Fixtures\Standalone\Policies\StandaloneRecordPolicy;
use FluxErp\Tests\Fixtures\Standalone\StandaloneRecord;
use Illuminate\Support\Facades\Gate;

test('resolves a core policy for a core model', function (): void {
    expect(Gate::getPolicyFor(MailAccount::class))->toBeInstanceOf(MailAccountPolicy::class);
});

test('resolves the parent policy for a derived instance model without an own policy', function (): void {
    expect(Gate::getPolicyFor(InstanceMailAccount::class))->toBeInstanceOf(MailAccountPolicy::class);
});

test('prefers the conventionally-placed policy of a derived model over the parent policy', function (): void {
    expect(Gate::getPolicyFor(OverriddenMailAccount::class))
        ->toBeInstanceOf(OverriddenMailAccountPolicy::class);
});

test('an explicit registration on the parent class wins for derived models', function (): void {
    Gate::policy(MailAccount::class, OverriddenMailAccountPolicy::class);

    expect(Gate::getPolicyFor(InstanceMailAccount::class))
        ->toBeInstanceOf(OverriddenMailAccountPolicy::class);
});

test('resolves no policy for a model without a policy class', function (): void {
    expect(Gate::getPolicyFor(RecordWithoutPolicy::class))->toBeNull();
});

test('resolves no policy for a non-class string', function (): void {
    expect(Gate::getPolicyFor('not-a-class'))->toBeNull();
});

test('default guessing still resolves policies outside the Models convention', function (): void {
    expect(Gate::getPolicyFor(StandaloneRecord::class))->toBeInstanceOf(StandaloneRecordPolicy::class);
});
