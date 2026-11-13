<?php

namespace FluxErp\Traits\Action;

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Support\Calculation\DepreciationScheduleGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

trait ValidatesFixedAsset
{
    protected function validateFixedAsset(FixedAsset $fixedAsset, array $keys): array
    {
        $attributes = $fixedAsset->getAttributes();
        $tenantId = data_get($attributes, 'tenant_id');
        $errors = [];

        if (in_array('contact_id', $keys)
            && ! is_null(data_get($attributes, 'contact_id'))
            && resolve_static(Contact::class, 'query')
                ->whereKey(data_get($attributes, 'contact_id'))
                ->whereHasTenant($tenantId)
                ->doesntExist()
        ) {
            $errors['contact_id'] = [
                __('The selected :attribute must belong to the fixed asset tenant.', ['attribute' => 'contact_id']),
            ];
        }

        foreach ([
            'ledger_account_id' => LedgerAccountTypeEnum::Asset,
            'depreciation_ledger_account_id' => LedgerAccountTypeEnum::Expense,
        ] as $attribute => $type) {
            if (in_array($attribute, $keys)
                && resolve_static(LedgerAccount::class, 'query')
                    ->withoutGlobalScopes()
                    ->whereKey(data_get($attributes, $attribute))
                    ->where('tenant_id', $tenantId)
                    ->where('ledger_account_type_enum', $type)
                    ->doesntExist()
            ) {
                $errors[$attribute] = [
                    __('The selected :attribute must be a :type account of the fixed asset tenant.', [
                        'attribute' => $attribute,
                        'type' => __($type->name),
                    ]),
                ];
            }
        }

        if (in_array('order_id', $keys)
            && ! is_null(data_get($attributes, 'order_id'))
            && resolve_static(Order::class, 'query')
                ->withoutGlobalScopes()
                ->whereKey(data_get($attributes, 'order_id'))
                ->where('tenant_id', $tenantId)
                ->doesntExist()
        ) {
            $errors['order_id'] = [
                __('The selected :attribute must belong to the fixed asset tenant.', ['attribute' => 'order_id']),
            ];
        }

        if (array_intersect(['order_id', 'order_position_id'], $keys)
            && ! is_null(data_get($attributes, 'order_position_id'))
            && resolve_static(OrderPosition::class, 'query')
                ->withoutGlobalScopes()
                ->whereKey(data_get($attributes, 'order_position_id'))
                ->where('tenant_id', $tenantId)
                ->when(
                    ! is_null(data_get($attributes, 'order_id')),
                    fn (Builder $query): Builder => $query->where('order_id', data_get($attributes, 'order_id'))
                )
                ->doesntExist()
        ) {
            $errors['order_position_id'] = [
                __('The selected :attribute must belong to the order of the fixed asset.', ['attribute' => 'order_position_id']),
            ];
        }

        if (bccomp(
            (string) data_get($attributes, 'residual_value'),
            (string) data_get($attributes, 'acquisition_cost'),
            2
        ) !== -1) {
            $errors['residual_value'] = [__('The residual value must be lower than the acquisition cost.')];
        }

        if ($fixedAsset->depreciation_starts_at->lt($fixedAsset->acquired_at)) {
            $errors['depreciation_starts_at'] = [__('The depreciation cannot start before the acquisition.')];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        try {
            return app(DepreciationScheduleGenerator::class)->generate(
                data_get($attributes, 'acquisition_cost'),
                data_get($attributes, 'residual_value'),
                $fixedAsset->useful_life_months,
                $fixedAsset->depreciation_method_enum,
                $fixedAsset->depreciation_starts_at,
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'useful_life_months' => [__($e->getMessage())],
            ]);
        }
    }
}
