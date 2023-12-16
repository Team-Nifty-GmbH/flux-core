<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\OrderPosition;
use FluxErp\Rules\Numeric;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;

class CreateOrderPositionRequest extends BaseFormRequest
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
                'uuid' => 'string|uuid|unique:order_positions,uuid',
                'client_id' => 'required_without:order_id|integer|exists:clients,id,deleted_at,NULL',
                'ledger_account_id' => 'integer|nullable|exists:ledger_accounts,id',
                'order_id' => 'required|integer|exists:orders,id,deleted_at,NULL',
                'origin_position_id' => 'integer|nullable|exists:order_positions,id,deleted_at,NULL',
                'parent_id' => 'sometimes|integer|nullable|exists:order_positions,id,deleted_at,NULL',
                'price_id' => [
                    'exclude_if:is_free_text,true',
                    'exclude_if:is_bundle_position,true',
                    'exclude_without:product_id',
                    'integer',
                    'nullable',
                    'exists:prices,id,deleted_at,NULL',
                ],
                'price_list_id' => [
                    'exclude_if:is_free_text,true',
                    'exclude_if:is_bundle_position,true',
                    'integer',
                    'nullable',
                    'exists:price_lists,id,deleted_at,NULL',
                ],
                'product_id' => [
                    Rule::when(
                        fn (Fluent $data) => $data->is_free_text === true
                            && $data->get('is_bundle_position', false) === false,
                        'exclude'
                    ),
                    'integer',
                    'nullable',
                    'exists:products,id,deleted_at,NULL',
                ],
                'supplier_contact_id' => 'integer|nullable|exists:contacts,id,deleted_at,NULL',
                'vat_rate_id' => [
                    'exclude_if:is_free_text,true',
                    'exclude_if:is_bundle_position,true',
                    'required_if:is_free_text,false',
                    'required_if:is_bundle_position,false',
                    'integer',
                    'nullable',
                    'exists:vat_rates,id,deleted_at,NULL',
                ],
                'warehouse_id' => [
                    'exclude_if:is_free_text,true',
                    'required_with:product_id',
                    'integer',
                    'nullable',
                    'exists:warehouses,id,deleted_at,NULL',
                ],

                'amount' => [
                    'exclude_if:is_free_text,true',
                    new Numeric(),
                    'nullable',
                ],
                'margin' => [
                    'exclude_if:is_free_text,true',
                    new Numeric(),
                    'nullable',
                ],
                'provision' => [
                    new Numeric(),
                    'nullable',
                ],
                'purchase_price' => [
                    new Numeric(),
                    'nullable',
                ],
                'unit_price' => [
                    'exclude_if:is_free_text,true',
                    'exclude_if:is_bundle_position,true',
                    'required_without_all:product_id,price_list_id,price_id',
                    new Numeric(),
                    'nullable',
                ],

                'amount_packed_products' => [
                    new Numeric(),
                    'nullable',
                ],
                'customer_delivery_date' => 'date_format:Y-m-d|nullable',
                'ean_code' => 'string|nullable',
                'possible_delivery_date' => 'date_format:Y-m-d|nullable',
                'unit_gram_weight' => [
                    new Numeric(),
                    'nullable',
                ],

                'description' => 'string|nullable',
                'name' => 'required_without:product_id|string',
                'product_number' => [
                    'exclude_if:is_free_text,true',
                    'exclude_with:product_id',
                    'nullable',
                    'string',
                ],
                'sort_number' => 'nullable|integer|min:0',

                'is_alternative' => 'boolean',
                'is_net' => [
                    'exclude_if:is_free_text,true',
                    'exclude_if:is_bundle_position,true',
                    'required_if:is_free_text,false',
                    'required_if:is_bundle_position,false',
                ],
                'is_free_text' => 'boolean',
                'is_bundle_position' => 'exclude_without:parent_id|boolean',

                'discounts' => 'array',
                'discounts.*.sort_number' => 'required|integer|min:0',
                'discounts.*.is_percentage' => 'required|boolean',
                'discounts.*.discount' => [
                    'required',
                    new Numeric(),
                ],

                'tags' => 'array',
                'tags.*' => 'string',
            ],
        );
    }
}
