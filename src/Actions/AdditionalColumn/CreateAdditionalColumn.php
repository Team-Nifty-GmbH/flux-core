<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateAdditionalColumnRequest;
use FluxErp\Models\AdditionalColumn;

class CreateAdditionalColumn extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateAdditionalColumnRequest())->rules();
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function performAction(): AdditionalColumn
    {
        if (! ($this->data['validations'] ?? false)) {
            $this->data['validations'] = null;
        }

        if (! ($this->data['values'] ?? false)) {
            $this->data['values'] = null;
        }

        $additionalColumn = new AdditionalColumn($this->data);
        $additionalColumn->save();

        return $additionalColumn;
    }
}
