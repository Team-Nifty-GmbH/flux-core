<?php

namespace FluxErp\Actions\PriceList;

use FluxErp\Actions\BaseAction;
use FluxErp\Helpers\Helper;
use FluxErp\Http\Requests\UpdatePriceListRequest;
use FluxErp\Models\PriceList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdatePriceList extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdatePriceListRequest())->rules();
    }

    public static function models(): array
    {
        return [PriceList::class];
    }

    public function performAction(): Model
    {
        $priceList = PriceList::query()
            ->whereKey($this->data['id'])
            ->first();

        $priceList->fill($this->data);
        $priceList->save();

        return $priceList->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        parent::validateData();

        // Check if new parent causes a cycle
        if (
            ($this->data['parent_id'] ?? false)
            && Helper::checkCycle(
                model: PriceList::class,
                item: PriceList::query()->whereKey($this->data['id'])->first(),
                parentId: $this->data['parent_id']
            )
        ) {
            throw ValidationException::withMessages([
                'parent_id' => [__('Cycle detected')],
            ])->errorBag('updatePriceList');
        }
    }
}
