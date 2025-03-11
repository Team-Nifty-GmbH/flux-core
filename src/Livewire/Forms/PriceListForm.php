<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\PriceList\CreatePriceList;
use FluxErp\Actions\PriceList\DeletePriceList;
use FluxErp\Actions\PriceList\UpdatePriceList;
use FluxErp\Models\PriceList;
use Livewire\Attributes\Locked;

class PriceListForm extends FluxForm
{
    public ?array $discount = [
        'discount' => null,
        'is_percentage' => true,
    ];

    #[Locked]
    public ?int $id = null;

    public ?bool $is_default = false;

    public ?bool $is_net = true;

    public ?bool $is_purchase = false;

    public ?string $name = null;

    public ?int $parent_id = null;

    public ?string $price_list_code = null;

    public ?string $rounding_method_enum = 'none';

    public ?string $rounding_mode = null;

    public ?int $rounding_number = null;

    public ?int $rounding_precision = null;

    protected ?string $modelClass = PriceList::class;

    public function fill($values): void
    {
        if ($values instanceof PriceList) {
            $values->loadMissing(['discount:id,model_type,model_id,discount,is_percentage']);
        }

        if (data_get($values, 'discount.is_percentage')) {
            data_set($values, 'discount.discount', data_get($values, 'discount.discount') * 100);
        }

        parent::fill($values);

        if (is_null($this->discount)) {
            $this->discount = [
                'discount' => null,
                'is_percentage' => true,
            ];
        }
    }

    protected function getActions(): array
    {
        return [
            'create' => CreatePriceList::class,
            'update' => UpdatePriceList::class,
            'delete' => DeletePriceList::class,
        ];
    }

    protected function makeAction(string $name, ?array $data = null): FluxAction
    {
        $data = $data ?? $this->toArray();

        if (data_get($data, 'discount.is_percentage')) {
            data_set($data, 'discount.discount', data_get($data, 'discount.discount') / 100);
        }

        return parent::makeAction($name, $data);
    }
}
