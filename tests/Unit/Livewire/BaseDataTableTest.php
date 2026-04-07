<?php

use FluxErp\Livewire\DataTables\BaseDataTable;
use TeamNiftyGmbH\DataTable\Traits\DataTables\SupportsExporting;

test('export method signature matches parent class', function (): void {
    $parentMethod = new ReflectionMethod(SupportsExporting::class, 'export');
    $childMethod = new ReflectionMethod(BaseDataTable::class, 'export');

    // Parameter count must match
    expect($childMethod->getNumberOfParameters())
        ->toBe($parentMethod->getNumberOfParameters(), 'export() parameter count mismatch');

    // Each parameter name and type must match
    foreach ($parentMethod->getParameters() as $i => $parentParam) {
        $childParam = $childMethod->getParameters()[$i];

        expect($childParam->getName())->toBe($parentParam->getName());
        expect((string) $childParam->getType())->toBe((string) $parentParam->getType());
    }

    // Return type must be compatible
    $parentReturn = (string) $parentMethod->getReturnType();
    $childReturn = (string) $childMethod->getReturnType();

    expect($childReturn)->toBe($parentReturn, 'export() return type mismatch');
});
