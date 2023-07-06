<?php

namespace FluxErp\Actions\Presentation;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Helpers\Helper;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreatePresentationRequest;
use FluxErp\Models\Presentation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreatePresentation implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreatePresentationRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'presentation.create';
    }

    public static function description(): string|null
    {
        return 'create presentation';
    }

    public static function models(): array
    {
        return [Presentation::class];
    }

    public function execute(): Presentation
    {
        $presentation = new Presentation($this->data);
        $presentation->save();

        return $presentation;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
