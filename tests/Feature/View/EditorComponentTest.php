<?php

use FluxErp\Facades\Editor;
use FluxErp\Models\Order;
use FluxErp\View\Components\Editor as EditorComponent;
use FluxErp\View\Components\EditorButtons\ImageUpload;
use Illuminate\Support\Facades\Blade;

test('the editor renders blade variables in the tooltip dropdown mode', function (): void {
    $html = Blade::render(
        '<x-flux::editor :tooltip-dropdown="true" :buttons="[\'bold\', \'blade-variables\']" :blade-variables="$variables" />',
        ['variables' => Editor::getTranslatedVariables(Order::class)]
    );

    expect($html)->toBeString()->not->toBeEmpty();
});

test('a button limited to scopes is not shown on an editor without a scope', function (): void {
    $button = new class() extends ImageUpload
    {
        public static function scopes(): array
        {
            return ['knowledge'];
        }
    };

    expect((new EditorComponent())->getButtonInstance($button::class))->toBeNull()
        ->and((new EditorComponent(scope: 'mail'))->getButtonInstance($button::class))->toBeNull()
        ->and((new EditorComponent(scope: 'knowledge'))->getButtonInstance($button::class))->not->toBeNull();
});
