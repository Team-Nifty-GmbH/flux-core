<?php

namespace FluxErp\Actions\Presentation;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdatePresentationRequest;
use FluxErp\Models\Presentation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdatePresentation extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdatePresentationRequest())->rules();
    }

    public static function models(): array
    {
        return [Presentation::class];
    }

    public function execute(): Model
    {
        $presentation = Presentation::query()
            ->whereKey($this->data['id'])
            ->first();

        $presentation->fill($this->data);
        $presentation->save();

        return $presentation->withoutRelations()->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Presentation());

        $this->data = $validator->validate();

        return $this;
    }
}
