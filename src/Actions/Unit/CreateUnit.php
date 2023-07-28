<?php

namespace FluxErp\Actions\Unit;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateUnitRequest;
use FluxErp\Models\Unit;

class CreateUnit extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateUnitRequest())->rules();
    }

    public static function models(): array
    {
        return [Unit::class];
    }

    public function execute(): Unit
    {
        $unit = new Unit($this->data);
        $unit->save();

        return $unit->fresh();
    }
}
