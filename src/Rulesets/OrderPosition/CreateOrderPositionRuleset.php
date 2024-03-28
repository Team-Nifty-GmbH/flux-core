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
            'uuid' => 'string|uuid|unique:order_positions,uuid',
            'client_id' => [
                'required_without:order_id',
                'integer',
                new ModelExists(Client::class),
            ],
            'ledger_account_id' => [
                'integer',
                'nullable',
                new ModelExists(LedgerAccount::class),
            ],
            'order_id' => [
                'required',
                'integer',
                new ModelExists(Order::class),
            ],
            'origin_position_id' => [
                'integer',
                'nullable',
                new ModelExists(OrderPosition::class),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                new ModelExists(OrderPosition::class),
            ],
            'price_id' => [
                'exclude_if:is_free_text,true',
                'exclude_if:is_bundle_position,true',
                'exclude_without:product_id',
                'integer',
                'nullable',
                new ModelExists(Price::class),
            ],
            'price_list_id' => [
                'exclude_if:is_free_text,true',
                'exclude_if:is_bundle_position,true',
                'integer',
                'nullable',
                new ModelExists(PriceList::class),
            ],
            'product_id' => [
                Rule::when(
                    fn (Fluent $data) => $data->is_free_text === true
                        && $data->get('is_bundle_position', false) === false,
                    'exclude'
                ),
                'integer',
                'nullable',
                new ModelExists(Product::class),
            ],
            'supplier_contact_id' => [
                'integer',
                'nullable',
                new ModelExists(Contact::class),
            ],
            'vat_rate_id' => [
                'exclude_if:is_free_text,true',
                'exclude_if:is_bundle_position,true',
                'required_if:is_free_text,false',
                'required_if:is_bundle_position,false',
                'integer',
                'nullable',
                new ModelExists(VatRate::class),
            ],
            'warehouse_id' => [
                'exclude_if:is_free_text,true',
                'integer',
                'nullable',
                new ModelExists(Warehouse::class),
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
                'exclude_if:is_free_text,true',
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

            'discount_percentage' => [
                new Numeric(0, 1),
                'nullable',
            ],
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
