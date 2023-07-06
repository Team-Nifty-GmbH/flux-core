<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateContactOptionRequest;
use FluxErp\Models\ContactOption;
use Illuminate\Support\Facades\Validator;

class CreateContactOption implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateContactOptionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'contact-option.create';
    }

    public static function description(): string|null
    {
        return 'create contact option';
    }

    public static function models(): array
    {
        return [ContactOption::class];
    }

    public function execute(): ContactOption
    {
        $contactOption = new ContactOption($this->data);
        $contactOption->save();

        return $contactOption;
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
