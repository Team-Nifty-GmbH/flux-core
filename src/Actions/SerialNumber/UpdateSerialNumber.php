<?php

namespace FluxErp\Actions\SerialNumber;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SerialNumber;
use FluxErp\Rulesets\SerialNumber\UpdateSerialNumberRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateSerialNumber extends FluxAction
{
    public static function models(): array
    {
        return [SerialNumber::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateSerialNumberRuleset::class;
    }

    public function performAction(): Model
    {
        $serialNumber = resolve_static(SerialNumber::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $serialNumber->fill($this->data);
        $serialNumber->save();

        return $serialNumber->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $serialNumber = resolve_static(SerialNumber::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if (
            ($this->data['product_id'] ?? false) &&
            $serialNumber->product_id &&
            $serialNumber->product_id !== $this->data['product_id']
        ) {
            $errors += ['product_id' => ['Serial number already has a product_id']];
        }

        if (
            ($this->data['order_position_id'] ?? false) &&
            $serialNumber->order_position_id &&
            $serialNumber->order_position_id !== $this->data['order_position_id']
        ) {
            $errors += ['order_position_id' => ['Serial number already has an order_position_id']];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('updateSerialNumber');
        }
    }
}
