<?php

namespace FluxErp\Actions\Currency;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Currency;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeleteCurrency implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:currencies,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'currency.delete';
    }

    public static function description(): string|null
    {
        return 'delete currency';
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function execute()
    {
        $currency =Currency::query()
            ->whereKey($this->data['id'])
            ->first();

        $currency->iso = $currency->iso . '___' . Hash::make(Str::uuid());
        $currency->save();

        return $currency->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        if (Currency::query()
                ->whereKey($this->data['id'])
                ->first()
                ->countries()
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'country' => [__('Currency referenced by a country')],
            ])->errorBag('deleteCurrency');
        }

        return $this;
    }
}
