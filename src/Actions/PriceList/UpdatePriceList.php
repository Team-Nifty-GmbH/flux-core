<?php

namespace FluxErp\Actions\PriceList;

use FluxErp\Actions\Discount\CreateDiscount;
use FluxErp\Actions\Discount\DeleteDiscount;
use FluxErp\Actions\Discount\UpdateDiscount;
use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\PriceList;
use FluxErp\Rulesets\PriceList\UpdatePriceListRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdatePriceList extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdatePriceListRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PriceList::class];
    }

    public function performAction(): Model
    {
        $priceList = resolve_static(PriceList::class, 'query')
            ->whereKey($this->data['id'])
            ->with(['discount'])
            ->first();

        $priceList->fill($this->data);
        $priceList->save();

        $hasDiscount = ($this->data['discount'] ?? false) && $this->data['discount']['discount'] != 0;
        if ($hasDiscount && ! $priceList->discount) {
            // Create new discount
            CreateDiscount::make(
                array_merge(
                    $this->data['discount'],
                    [
                        'model_type' => app(PriceList::class)->getMorphClass(),
                        'model_id' => $priceList->id,
                    ]
                )
            )->execute();
        } elseif ($hasDiscount && $priceList->discount) {
            // Update existing discount
            UpdateDiscount::make(
                array_merge(
                    $priceList->discount->toArray(),
                    $this->data['discount']
                )
            );
        } elseif ($priceList->discount && ! $hasDiscount) {
            // Delete existing discount
            DeleteDiscount::make(['id' => $priceList->discount->id])->execute();
        }

        return $priceList->withoutRelations()->fresh($hasDiscount ? ['discount'] : []);
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];

        // Check if new parent causes a cycle
        if (
            ($this->data['parent_id'] ?? false)
            && Helper::checkCycle(
                model: PriceList::class,
                item: resolve_static(PriceList::class, 'query')->whereKey($this->data['id'])->first(),
                parentId: $this->data['parent_id']
            )
        ) {
            $errors += [
                'parent_id' => [__('Cycle detected')],
            ];
        }

        // Check price_list_code unique
        if (($this->data['price_list_code'] ?? false)
            && resolve_static(PriceList::class, 'query')
                ->where('id', '!=', $this->data['id'])
                ->where('price_list_code', $this->data['price_list_code'])
                ->exists()
        ) {
            $errors += [
                'price_list_code' => [__('validation.unique', ['attribute' => 'price_list_code'])],
            ];
        }

        // Check discount is max 1 if is_percentage = true
        if (($this->data['discount'] ?? false)
            && $this->data['discount']['is_percentage']
            && $this->data['discount']['discount'] > 1
        ) {
            $errors += [
                'discount.discount' => [__('validation.max', ['attribute' => 'discount', 'max' => 1])],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('updatePriceList');
        }
    }
}
