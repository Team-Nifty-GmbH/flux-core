<?php

use FluxErp\States\Order\Done;
use FluxErp\States\Order\Draft;

test('done order state color is violet', function (): void {
    expect((new Done(''))->color())->toBe('violet');
});

test('done and draft order state colors are distinguishable', function (): void {
    expect((new Done(''))->color())->not->toBe((new Draft(''))->color());
});
