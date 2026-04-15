<?php

use FluxErp\Models\Contact;
use FluxErp\Models\DiscountGroup;
use FluxErp\RuleEngine\Conditions\ContactCondition;
use FluxErp\RuleEngine\Conditions\ContactDiscountGroupCondition;
use FluxErp\RuleEngine\Scopes\PriceScope;

// --- ContactCondition ---

test('contact condition matches when contact id is in list', function (): void {
    $contact = Contact::factory()->create();

    $condition = new ContactCondition();
    $condition->contact_ids = [$contact->getKey()];
    $condition->operator = 'in';

    $scope = new PriceScope(contact: $contact);

    expect($condition->match($scope))->toBeTrue();
});

test('contact condition does not match when contact id is not in list', function (): void {
    $contact = Contact::factory()->create();

    $condition = new ContactCondition();
    $condition->contact_ids = [999999];
    $condition->operator = 'in';

    $scope = new PriceScope(contact: $contact);

    expect($condition->match($scope))->toBeFalse();
});

test('contact condition matches not_in when contact id is not in list', function (): void {
    $contact = Contact::factory()->create();

    $condition = new ContactCondition();
    $condition->contact_ids = [999999];
    $condition->operator = 'not_in';

    $scope = new PriceScope(contact: $contact);

    expect($condition->match($scope))->toBeTrue();
});

test('contact condition returns false when no contact in scope', function (): void {
    $condition = new ContactCondition();
    $condition->contact_ids = [1];
    $condition->operator = 'in';

    $scope = new PriceScope();

    expect($condition->match($scope))->toBeFalse();
});

// --- ContactDiscountGroupCondition ---

test('contact discount group condition matches when contact has matching group', function (): void {
    $contact = Contact::factory()->create();
    $group = DiscountGroup::create(['name' => 'Test Group']);
    $contact->discountGroups()->attach($group->getKey());

    $condition = new ContactDiscountGroupCondition();
    $condition->group_ids = [$group->getKey()];
    $condition->operator = 'in';

    $scope = new PriceScope(contact: $contact);

    expect($condition->match($scope))->toBeTrue();
});

test('contact discount group condition does not match when contact has no matching group', function (): void {
    $contact = Contact::factory()->create();

    $condition = new ContactDiscountGroupCondition();
    $condition->group_ids = [999999];
    $condition->operator = 'in';

    $scope = new PriceScope(contact: $contact);

    expect($condition->match($scope))->toBeFalse();
});

test('contact discount group condition matches not_in when contact has no matching group', function (): void {
    $contact = Contact::factory()->create();

    $condition = new ContactDiscountGroupCondition();
    $condition->group_ids = [999999];
    $condition->operator = 'not_in';

    $scope = new PriceScope(contact: $contact);

    expect($condition->match($scope))->toBeTrue();
});

test('contact discount group condition returns false when no contact in scope', function (): void {
    $condition = new ContactDiscountGroupCondition();
    $condition->group_ids = [1];
    $condition->operator = 'in';

    $scope = new PriceScope();

    expect($condition->match($scope))->toBeFalse();
});
