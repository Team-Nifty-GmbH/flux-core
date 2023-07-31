<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateContactOptionRequest;
use FluxErp\Models\ContactOption;

class CreateContactOption extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateContactOptionRequest())->rules();
    }

    public static function models(): array
    {
        return [ContactOption::class];
    }

    public function performAction(): ContactOption
    {
        $contactOption = new ContactOption($this->data);
        $contactOption->save();

        return $contactOption;
    }
}
