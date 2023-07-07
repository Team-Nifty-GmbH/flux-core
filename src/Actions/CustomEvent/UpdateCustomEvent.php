<?php

namespace FluxErp\Actions\CustomEvent;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateCustomEventRequest;
use FluxErp\Models\CustomEvent;
use Illuminate\Database\Eloquent\Model;

class UpdateCustomEvent extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateCustomEventRequest())->rules();
    }

    public static function models(): array
    {
        return [CustomEvent::class];
    }

    public function execute(): Model
    {
        $customEvent = CustomEvent::query()
            ->whereKey($this->data['id'])
            ->first();

        $customEvent->fill($this->data);
        $customEvent->save();

        return $customEvent->withoutRelations()->fresh();
    }
}
