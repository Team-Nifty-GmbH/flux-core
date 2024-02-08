<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\Tag;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use FluxErp\Rules\ExistsWithIgnore;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;

class UpdateOrderPositionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new OrderPosition())->hasAdditionalColumnsValidationRules(),
            [
                'id' => [
                    'sometimes',
                    'required',
                    'integer',
                    new ModelExists(OrderPosition::class),
                ],
                'client_id' => [
                    'integer',
                    new ModelExists(Client::class),
                ],
                'ledger_account_id' => [
                    'integer',
                    'nullable',
                    new ModelExists(LedgerAccount::class),
                ],
                'order_id' => [
                    'integer',
                    new ModelExists(Order::class),
                ],
                'parent_id' => [
                    'integer',
                    'nullable',
                    new ModelExists(OrderPosition::class),
                ],
                'price_id' => [
                    'exclude_if:is_free_text,true',
                    'integer',
                    'nullable',
                    new ModelExists(Price::class),
                ],
                'price_list_id' => [
                    'exclude_if:is_free_text,true',
                    'exclude_if:is_bundle_position,true',
                    'integer',
                    new ModelExists(PriceList::class),
                ],
                'product_id' => [
                    Rule::when(
                        fn (Fluent $data) => $data->is_free_text === true
                            && $data->get('is_bundle_position', false) === false,
                        'exclude'
                    ),
                    'nullable',
                    'integer',
                    new ModelExists(Product::class),
                ],
                'supplier_contact_id' => [
                    'integer',
                    'nullable',
                    new ModelExists(Contact::class),
                ],
                'vat_rate_id' => [
                    'exclude_if:is_free_text,true',
                    'integer',
                    'nullable',
                    new ModelExists(VatRate::class),
                ],
                'warehouse_id' => [
                    'exclude_if:is_free_text,true',
                    'sometimes',
                    'required_with:product_id',
                    'integer',
                    'nullable',
                    new ModelExists(Warehouse::class),
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
                    'exclude_with:product_id',
                    'sometimes',
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
                'tags.*' => [
                    'required',
                    'integer',
                    (new ModelExists(Tag::class))->where('type', OrderPosition::class),
                ],
            ],
        );
    }
}
