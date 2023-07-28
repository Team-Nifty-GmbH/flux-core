<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateContactOptionRequest;
use FluxErp\Models\ContactOption;

class CreateContactOption extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateContactOptionRequest())->rules();
    }

    public static function models(): array
    {
        return [ContactOption::class];
    }

    public function execute(): ContactOption
    {
        $contactOption = new ContactOption($this->data);
        $contactOption->save();

        return $contactOption->fresh();
    }
}
