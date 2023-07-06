<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\ContactOption;
use Illuminate\Support\Facades\Validator;

class DeleteContactOption implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:contact_options,id',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'contact-option.delete';
    }

    public static function description(): string|null
    {
        return 'delete contact option';
    }

    public static function models(): array
    {
        return [ContactOption::class];
    }

    public function execute(): bool|null
    {
        return ContactOption::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
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
