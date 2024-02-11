<?php

namespace FluxErp\Actions\CustomEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateCustomEventRequest;
use FluxErp\Models\CustomEvent;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated
 */
class UpdateCustomEvent extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateCustomEventRequest())->rules();

        $this->rules['name'] = $this->rules['name'] . ',' . $this->data['id'];
    }

    public static function models(): array
    {
        return [CustomEvent::class];
    }

    public function performAction(): Model
    {
        $customEvent = CustomEvent::query()
            ->whereKey($this->data['id'])
            ->first();

        $customEvent->fill($this->data);
        $customEvent->save();

        return $customEvent->withoutRelations()->fresh();
    }
}
