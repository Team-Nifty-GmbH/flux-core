<?php

namespace FluxErp\Actions\Presentation;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdatePresentationRequest;
use FluxErp\Models\Presentation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdatePresentation extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdatePresentationRequest())->rules();
    }

    public static function models(): array
    {
        return [Presentation::class];
    }

    public function performAction(): Model
    {
        $presentation = Presentation::query()
            ->whereKey($this->data['id'])
            ->first();

        $presentation->fill($this->data);
        $presentation->save();

        return $presentation->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Presentation());

        $this->data = $validator->validate();
    }
}
