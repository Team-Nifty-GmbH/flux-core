<?php

namespace FluxErp\Actions\CustomEvent;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateCustomEventRequest;
use FluxErp\Models\CustomEvent;

class CreateCustomEvent extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateCustomEventRequest())->rules();
    }

    public static function models(): array
    {
        return [CustomEvent::class];
    }

    public function execute(): CustomEvent
    {
        $customEvent = new CustomEvent($this->data);
        $customEvent->save();

        return $customEvent;
    }
}
