<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateVatRateRequest;
use FluxErp\Models\VatRate;
use Illuminate\Support\Facades\Validator;

class CreateVatRate implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateVatRateRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'vat-rate.create';
    }

    public static function description(): string|null
    {
        return 'create vat rate';
    }

    public static function models(): array
    {
        return [VatRate::class];
    }

    public function execute(): VatRate
    {
        $vatRate = new VatRate($this->data);
        $vatRate->save();

        return $vatRate;
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
