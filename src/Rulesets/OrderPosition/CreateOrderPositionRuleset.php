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

class CreateOrderPositionRuleset extends FluxRuleset
{
    protected static ?string $model = OrderPosition::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:order_positions,uuid',
            'client_id' => [
                'required_without:order_id',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'ledger_account_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'order_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'origin_position_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'price_id' => [
                'exclude_if:is_free_text,true',
                'exclude_if:is_bundle_position,true',
                'exclude_without:product_id',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Price::class]),
            ],
            'price_list_id' => [
                'exclude_if:is_free_text,true',
                'exclude_if:is_bundle_position,true',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'product_id' => [
                Rule::when(
                    fn (Fluent $data) => $data->is_free_text === true
                        && $data->get('is_bundle_position', false) === false,
                    'exclude'
                ),
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'supplier_contact_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'vat_rate_id' => [
                'exclude_if:is_free_text,true',
                'exclude_if:is_bundle_position,true',
                Rule::when(
                    fn (Fluent $data) => (! $data->is_free_text
                        && ! $data->is_bundle_position)
                        && ! resolve_static(Product::class, 'query')->whereKey($data->product_id)->exists(),
                    'required'
                ),
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => VatRate::class]),
            ],
            'warehouse_id' => [
                'exclude_if:is_free_text,true',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Warehouse::class]),
            ],

            'amount' => [
                'exclude_if:is_free_text,true',
                app(Numeric::class),
                'nullable',
            ],
            'discount_percentage' => [
                app(Numeric::class, ['min' => 0, 'max' => 1]),
                'nullable',
            ],
            'margin' => [
                'exclude_if:is_free_text,true',
                app(Numeric::class),
                'nullable',
            ],
            'provision' => [
                app(Numeric::class),
                'nullable',
            ],
            'purchase_price' => [
                app(Numeric::class),
                'nullable',
                'exclude_if:is_free_text,true',
            ],
            'unit_price' => [
                'exclude_if:is_free_text,true',
                'exclude_if:is_bundle_position,true',
                'required_without_all:product_id,price_list_id,price_id',
                app(Numeric::class),
                'nullable',
            ],

            'amount_packed_products' => [
                app(Numeric::class),
                'nullable',
            ],
            'customer_delivery_date' => 'date|nullable',
            'ean_code' => 'string|nullable',
            'possible_delivery_date' => 'date|nullable',
            'unit_gram_weight' => [
                app(Numeric::class),
                'nullable',
            ],

            'description' => 'string|nullable',
            'name' => 'required_without:product_id|string|max:255',
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
                'boolean',
            ],
            'is_free_text' => 'boolean',
            'is_bundle_position' => 'exclude_without:parent_id|boolean',
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
