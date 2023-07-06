<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateVatRateRequest;
use FluxErp\Models\VatRate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateVatRate implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateVatRateRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'vat-rate.update';
    }

    public static function description(): string|null
    {
        return 'update vat rate';
    }

    public static function models(): array
    {
        return [VatRate::class];
    }

    public function execute(): Model
    {
        $vatRate = VatRate::query()
            ->whereKey($this->data['id'])
            ->first();

        $vatRate->fill($this->data);
        $vatRate->save();

        return $vatRate->withoutRelations()->fresh();
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
