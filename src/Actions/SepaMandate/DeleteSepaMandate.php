<?php

namespace FluxErp\Actions\SepaMandate;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\SepaMandate;
use Illuminate\Support\Facades\Validator;

class DeleteSepaMandate implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:sepa_mandates,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'sepa-mandate.delete';
    }

    public static function description(): string|null
    {
        return 'delete sepa mandate';
    }

    public static function models(): array
    {
        return [SepaMandate::class];
    }

    public function execute(): bool|null
    {
        return SepaMandate::query()
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
