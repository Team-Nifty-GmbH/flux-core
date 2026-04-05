<?php

test('hr dashboard loads without js errors', function (): void {
    visit(route('human-resources.dashboard'))
        ->assertNoSmoke();
});

test('employees list loads without js errors', function (): void {
    visit(route('human-resources.employees'))
        ->assertNoSmoke();
});

test('employees list shows data table', function (): void {
    visit(route('human-resources.employees'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('work times page loads without js errors', function (): void {
    visit(route('human-resources.work-times'))
        ->assertNoSmoke();
});

test('work times shows data table', function (): void {
    visit(route('human-resources.work-times'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('absence requests page loads without js errors', function (): void {
    visit(route('human-resources.absence-requests'))
        ->assertNoSmoke();
});

test('absence requests shows data table', function (): void {
    visit(route('human-resources.absence-requests'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('attendance overview page loads without js errors', function (): void {
    visit(route('human-resources.attendance-overview'))
        ->assertNoSmoke();
});

test('employee days page loads without js errors', function (): void {
    visit(route('human-resources.employee-days'))
        ->assertNoSmoke();
});

test('employee days shows data table', function (): void {
    visit(route('human-resources.employee-days'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});
