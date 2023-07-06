<?php

namespace FluxErp\Actions\Presentation;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdatePresentationRequest;
use FluxErp\Models\Presentation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdatePresentation implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdatePresentationRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'presentation.update';
    }

    public static function description(): string|null
    {
        return 'update presentation';
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

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Presentation());

        $this->data = $validator->validate();

        return $this;
    }
}
