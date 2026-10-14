<?php

namespace FluxErp\Tests\Fixtures\Actions;

use FluxErp\Actions\FluxAction;
use FluxErp\Traits\Action\BroadcastsActionEvents;

class CreateBroadcastFixture extends FluxAction
{
    use BroadcastsActionEvents;

    public static function models(): array
    {
        return [];
    }

    public function performAction(): mixed
    {
        return null;
    }
}
