<?php

namespace FluxErp\Rulesets\OrderPosition;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;

class UpdateOrderPositionRuleset extends FluxRuleset
{
    protected static ?string $model = OrderPosition::class;

    public function rules(): array
    {
        return [
            'id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'client_id' => [
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'ledger_account_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'order_id' => [
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'price_id' => [
                'exclude_if:is_free_text,true',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Price::class]),
            ],
            'price_list_id' => [
                'exclude_if:is_free_text,true',
                'exclude_if:is_bundle_position,true',
                'integer',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'product_id' => [
                Rule::when(
                    fn (Fluent $data) => $data->is_free_text === true
                        && $data->get('is_bundle_position', false) === false,
                    'exclude'
                ),
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'supplier_contact_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'vat_rate_id' => [
                'exclude_if:is_free_text,true',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => VatRate::class]),
            ],
            'warehouse_id' => [
                'exclude_if:is_free_text,true',
                'sometimes',
                'required_with:product_id',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Warehouse::class]),
            ],

            'amount' => 'sometimes|numeric|nullable|exclude_if:is_free_text,true',
            'amount_bundle' => [
                'exclude_if:is_bundle_position,false',
                'required_if:is_bundle_position,true',
                'numeric',
                'nullable',
            ],
            'discount_percentage' => [
                app(Numeric::class, ['min' => 0, 'max' => 1]),
                'nullable',
            ],
            'margin' => 'exclude_if:is_free_text,true|sometimes|numeric|nullable',
            'provision' => 'numeric|nullable',
            'purchase_price' => [
                app(Numeric::class),
                'nullable',
            ],
            'unit_price' => 'numeric|nullable',

            'amount_packed_products' => 'numeric|nullable',
            'customer_delivery_date' => 'date|nullable',
            'ean_code' => 'string|nullable',
            'possible_delivery_date' => 'date|nullable',
            'unit_gram_weight' => 'numeric|nullable',

            'description' => 'string|nullable',
            'name' => 'sometimes|required|string|max:255',
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
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(DiscountRuleset::class, 'getRules'),
            resolve_static(TagRuleset::class, 'getRules')
        );
    }
}
