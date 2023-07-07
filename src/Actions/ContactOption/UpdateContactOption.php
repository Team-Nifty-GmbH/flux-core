<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateContactOptionRequest;
use FluxErp\Models\ContactOption;
use Illuminate\Database\Eloquent\Model;

class UpdateContactOption extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateContactOptionRequest())->rules();
    }

    public static function models(): array
    {
        return [ContactOption::class];
    }

    public function execute(): Model
    {
        $contactOption = ContactOption::query()
            ->whereKey($this->data['id'])
            ->first();

        $contactOption->fill($this->data);
        $contactOption->save();

        return $contactOption->withoutRelations()->fresh();
    }
}
