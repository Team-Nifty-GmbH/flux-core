<?php

namespace FluxErp\Actions\CustomEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateCustomEventRequest;
use FluxErp\Models\CustomEvent;

class CreateCustomEvent extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateCustomEventRequest())->rules();
    }

    public static function models(): array
    {
        return [CustomEvent::class];
    }

    public function performAction(): CustomEvent
    {
        $customEvent = new CustomEvent($this->data);
        $customEvent->save();

        return $customEvent->fresh();
    }
}
