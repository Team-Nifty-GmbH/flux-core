<?php

use FluxErp\Support\Editor\EditorManager;

beforeEach(function (): void {
    EditorManager::clearVariables();
});

test('mergeVariables wraps string values with auto-generated id', function (): void {
    EditorManager::mergeVariables([
        'Invoice Number' => '$order->invoice_number',
        'Order Date' => '$order->order_date',
    ], \FluxErp\Models\Order::class);

    $all = EditorManager::allVariables();
    $orderVars = $all[morph_alias(\FluxErp\Models\Order::class)];

    expect($orderVars['Invoice Number'])->toBe([
        'id' => morph_alias(\FluxErp\Models\Order::class) . '.invoice_number',
        'expression' => '$order->invoice_number',
    ]);
    expect($orderVars['Order Date'])->toBe([
        'id' => morph_alias(\FluxErp\Models\Order::class) . '.order_date',
        'expression' => '$order->order_date',
    ]);
});

test('mergeVariables wraps values with path segment in id', function (): void {
    EditorManager::mergeVariables([
        'Start Date' => '$order->order_date',
    ], \FluxErp\Models\Order::class, 'subscription');

    $all = EditorManager::allVariables();
    $morphAlias = morph_alias(\FluxErp\Models\Order::class);
    $subVars = $all[$morphAlias]['subscription'];

    expect($subVars['Start Date'])->toBe([
        'id' => $morphAlias . '.subscription.start_date',
        'expression' => '$order->order_date',
    ]);
});

test('mergeVariables passes through array values with explicit id', function (): void {
    EditorManager::mergeVariables([
        'Renamed Label' => ['expression' => '$order->invoice_number', 'id' => 'order.invoice_number'],
    ], \FluxErp\Models\Order::class);

    $all = EditorManager::allVariables();
    $morphAlias = morph_alias(\FluxErp\Models\Order::class);

    expect($all[$morphAlias]['Renamed Label'])->toBe([
        'expression' => '$order->invoice_number',
        'id' => 'order.invoice_number',
    ]);
});

test('mergeVariables global variables get __global__ prefix', function (): void {
    EditorManager::mergeVariables([
        'Current User Name' => 'auth()->user()?->name',
    ]);

    $all = EditorManager::allVariables();

    expect($all['__global__']['Current User Name'])->toBe([
        'id' => '__global__.current_user_name',
        'expression' => 'auth()->user()?->name',
    ]);
});

test('registerVariables wraps string values with auto-generated id', function (): void {
    EditorManager::registerVariables([
        'Custom Var' => '$model->custom',
    ], \FluxErp\Models\Order::class);

    $all = EditorManager::allVariables();
    $morphAlias = morph_alias(\FluxErp\Models\Order::class);

    expect($all[$morphAlias]['Custom Var'])->toBe([
        'id' => $morphAlias . '.custom_var',
        'expression' => '$model->custom',
    ]);
});

test('registerVariable wraps a single variable', function (): void {
    EditorManager::registerVariable('My Var', '$model->foo', \FluxErp\Models\Order::class);

    $all = EditorManager::allVariables();
    $morphAlias = morph_alias(\FluxErp\Models\Order::class);

    expect($all[$morphAlias]['My Var'])->toBe([
        'id' => $morphAlias . '.my_var',
        'expression' => '$model->foo',
    ]);
});

test('addVariable wraps raw string as null-id entry', function (): void {
    EditorManager::addVariable('$order->foo', \FluxErp\Models\Order::class);

    $all = EditorManager::allVariables();
    $morphAlias = morph_alias(\FluxErp\Models\Order::class);

    expect($all[$morphAlias][0])->toBe([
        'id' => null,
        'expression' => '$order->foo',
    ]);
});

test('addVariable passes through structured array', function (): void {
    $entry = ['id' => 'custom.id', 'expression' => '$order->foo'];
    EditorManager::addVariable($entry, \FluxErp\Models\Order::class);

    $all = EditorManager::allVariables();
    $morphAlias = morph_alias(\FluxErp\Models\Order::class);

    expect($all[$morphAlias][0])->toBe($entry);
});

test('setVariable wraps raw string as null-id entry', function (): void {
    EditorManager::setVariable('$order->foo', \FluxErp\Models\Order::class);

    $all = EditorManager::allVariables();
    $morphAlias = morph_alias(\FluxErp\Models\Order::class);

    expect($all[$morphAlias])->toBe([
        'id' => null,
        'expression' => '$order->foo',
    ]);
});
