<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\OrderPosition;
use FluxErp\Rules\ExistsWithIgnore;
use FluxErp\Rules\Numeric;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;

class UpdateOrderPositionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new OrderPosition())->hasAdditionalColumnsValidationRules(),
            [
                'id' => 'sometimes|required|integer|exists:order_positions,id,deleted_at,NULL',
                'client_id' => [
                    'integer',
                    (new ExistsWithIgnore('clients', 'id'))->whereNull('deleted_at'),
                ],
                'ledger_account_id' => 'integer|nullable|exists:ledger_accounts,id',
                'order_id' => [
                    'integer',
                    (new ExistsWithIgnore('orders', 'id'))->whereNull('deleted_at'),
                ],
                'parent_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('order_positions', 'id'))->whereNull('deleted_at'),
                ],
                'price_id' => [
                    'exclude_if:is_free_text,true',
                    'required_without_all:product_id,price_list_id,unit_price',
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('prices', 'id'))->whereNull('deleted_at'),
                ],
                'price_list_id' => [
                    'exclude_if:is_free_text,true',
                    'required_with:vat_rate_id',
                    'integer',
                    (new ExistsWithIgnore('price_lists', 'id'))->whereNull('deleted_at'),
                ],
                'product_id' => [
                    Rule::when(
                        fn (Fluent $data) => $data->is_free_text === true
                            && $data->get('is_bundle_position', false) === false,
                        'exclude'
                    ),
                    'nullable',
                    'integer',
                    (new ExistsWithIgnore('products', 'id'))->whereNull('deleted_at'),
                ],
                'supplier_contact_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('clients', 'id'))->whereNull('deleted_at'),
                ],
                'vat_rate_id' => [
                    'exclude_if:is_free_text,true',
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('vat_rates', 'id'))->whereNull('deleted_at'),
                ],
                'warehouse_id' => [
                    'exclude_if:is_free_text,true',
                    'sometimes',
                    'required_with:product_id',
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('warehouses', 'id'))->whereNull('deleted_at'),
                ],

                'amount' => 'sometimes|numeric|nullable|exclude_if:is_free_text,true',
                'amount_bundle' => [
                    'exclude_if:is_bundle_position,false',
                    'required_if:is_bundle_position,true',
                    'numeric',
                    'nullable',
                ],
                'margin' => 'exclude_if:is_free_text,true|sometimes|numeric|nullable',
                'provision' => 'numeric|nullable',
                'purchase_price' => [
                    new Numeric(),
                    'nullable',
                ],
                'unit_price' => 'numeric|nullable',

                'amount_packed_products' => 'numeric|nullable',
                'customer_delivery_date' => 'date_format:Y-m-d|nullable',
                'ean_code' => 'string|nullable',
                'possible_delivery_date' => 'date_format:Y-m-d|nullable',
                'unit_gram_weight' => 'numeric|nullable',

                'description' => 'string|nullable',
                'name' => 'sometimes|required|string',
                'product_number' => [
                    'exclude_if:is_free_text,true',
                    'sometimes',
                    'required_with:product_id',
                    'string',
                    'nullable',
                ],
                'sort_number' => 'integer|min:0',

                'is_alternative' => 'boolean',
                'is_net' => 'boolean',
                'is_free_text' => 'boolean',

                'discounts' => [
                    'array',
                ],
                'discounts.*.sort_number' => 'required|integer|min:0',
                'discounts.*.is_percentage' => 'required|boolean',
                'discounts.*.discount' => 'required|numeric',

                'tags' => 'array',
                'tags.*' => 'string',
            ],
        );
    }
}
