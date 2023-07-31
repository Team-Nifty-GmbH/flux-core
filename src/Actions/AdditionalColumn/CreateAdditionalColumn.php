<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateAdditionalColumnRequest;
use FluxErp\Models\AdditionalColumn;

class CreateAdditionalColumn extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateAdditionalColumnRequest())->rules();
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function execute(): AdditionalColumn
    {
        if (! ($this->data['validations'] ?? false)) {
            $this->data['validations'] = null;
        }

        if (! ($this->data['values'] ?? false)) {
            $this->data['values'] = null;
        }

        $additionalColumn = new AdditionalColumn($this->data);
        $additionalColumn->save();

        return $additionalColumn->fresh();
    }
}
