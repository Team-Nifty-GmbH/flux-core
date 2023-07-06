<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\StockPosting;
use Illuminate\Support\Facades\Validator;

class DeleteStockPosting implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:stock_postings,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'stock-posting.delete';
    }

    public static function description(): string|null
    {
        return 'delete stock posting';
    }

    public static function models(): array
    {
        return [StockPosting::class];
    }

    public function execute()
    {
        return StockPosting::query()
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
