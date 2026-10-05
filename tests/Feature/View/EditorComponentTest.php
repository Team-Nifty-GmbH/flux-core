<?php

use FluxErp\Facades\Editor;
use FluxErp\Models\Order;
use Illuminate\Support\Facades\Blade;

test('the editor renders blade variables in the tooltip dropdown mode', function (): void {
    $html = Blade::render(
        '<x-flux::editor :tooltip-dropdown="true" :buttons="[\'bold\', \'blade-variables\']" :blade-variables="$variables" />',
        ['variables' => Editor::getTranslatedVariables(Order::class)]
    );

    expect($html)->toBeString()->not->toBeEmpty();
});
