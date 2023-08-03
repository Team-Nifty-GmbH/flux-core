<?php

namespace FluxErp\Actions\Unit;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateUnitRequest;
use FluxErp\Models\Unit;

class CreateUnit extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateUnitRequest())->rules();
    }

    public static function models(): array
    {
        return [Unit::class];
    }

    public function performAction(): Unit
    {
        $unit = new Unit($this->data);
        $unit->save();

        return $unit->fresh();
    }
}
