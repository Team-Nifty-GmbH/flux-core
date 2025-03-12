<?php

namespace FluxErp\Actions\CustomEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateCustomEventRequest;
use FluxErp\Models\CustomEvent;

/**
 * @deprecated
 */
class CreateCustomEvent extends FluxAction
{
    public static function models(): array
    {
        return [CustomEvent::class];
    }

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateCustomEventRequest())->rules();
    }

    public function performAction(): CustomEvent
    {
        $customEvent = new CustomEvent($this->data);
        $customEvent->save();

        return $customEvent->fresh();
    }
}
