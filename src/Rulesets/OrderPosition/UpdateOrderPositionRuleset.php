<?php

namespace FluxErp\Rulesets\OrderPosition;

use FluxErp\Enums\CreditAccountPostingEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\Tenant;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;

class UpdateOrderPositionRuleset extends FluxRuleset
{
    protected static ?string $model = OrderPosition::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(DiscountRuleset::class, 'getRules'),
            resolve_static(TagRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'ledger_account_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => LedgerAccount::class, 'subject' => OrderPosition::class]),
            ],
            'order_id' => [
                'integer',
                app(ModelExists::class, ['model' => Order::class, 'subject' => OrderPosition::class]),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => OrderPosition::class, 'subject' => OrderPosition::class]),
            ],
            'price_id' => [
                'exclude_if:is_free_text,true',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Price::class, 'subject' => OrderPosition::class]),
            ],
            'price_list_id' => [
                'exclude_if:is_free_text,true',
                'exclude_if:is_bundle_position,true',
                'integer',
                app(ModelExists::class, ['model' => PriceList::class, 'subject' => OrderPosition::class]),
            ],
            'product_id' => [
                Rule::when(
                    fn (Fluent $data) => $data->is_free_text === true
                        && $data->get('is_bundle_position', false) === false,
                    'exclude'
                ),
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Product::class, 'subject' => OrderPosition::class])
                    ->where('is_variant_parent', false)
                    ->whereDoesntHave(
                        'children',
                        fn (Builder $query) => $query->where('is_active', true)
                    ),
            ],
            'supplier_contact_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Contact::class, 'subject' => OrderPosition::class]),
            ],
            'tenant_id' => [
                'integer',
                app(ModelExists::class, ['model' => Tenant::class, 'subject' => OrderPosition::class]),
            ],
            'vat_rate_id' => [
                'exclude_if:is_free_text,true',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => VatRate::class, 'subject' => OrderPosition::class]),
            ],
            'warehouse_id' => [
                'exclude_if:is_free_text,true',
                'sometimes',
                'required_with:product_id',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Warehouse::class, 'subject' => OrderPosition::class]),
            ],

            'amount' => [
                app(Numeric::class),
                'nullable',
            ],
            'amount_bundle' => [
                'exclude_if:is_bundle_position,false',
                'required_if:is_bundle_position,true',
                app(Numeric::class),
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
            'ean_code' => 'string|max:255|nullable',
            'possible_delivery_date' => 'date|nullable',
            'system_delivery_date' => 'date|nullable|required_with:system_delivery_date_end',
            'system_delivery_date_end' => 'date|nullable|after_or_equal:system_delivery_date',
            'unit_gram_weight' => 'numeric|nullable',

            'description' => 'string|nullable',
            'name' => 'sometimes|required|string|max:255',
            'product_number' => [
                'exclude_if:is_free_text,true',
                'exclude_with:product_id',
                'sometimes',
                'string',
                'max:255',
                'nullable',
            ],
            'sort_number' => 'integer|min:0',

            'credit_account_id' => [
                'exclude_if:is_free_text,true',
                'exclude_if:is_bundle_position,true',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => ContactBankConnection::class, 'subject' => OrderPosition::class]),
            ],
            'credit_amount' => [
                'exclude_if:is_free_text,true',
                'exclude_if:is_bundle_position,true',
                'exclude_without:credit_account_id',
                'exclude_if:credit_account_id,null',
                'required_with:credit_account_id',
                app(Numeric::class),
            ],
            'post_on_credit_account' => [
                'exclude_if:is_free_text,true',
                'exclude_if:is_bundle_position,true',
                'exclude_without:credit_account_id',
                'exclude_if:credit_account_id,null',
                'required_with:credit_account_id',
                'integer',
                Rule::enum(CreditAccountPostingEnum::class),
                'nullable',
            ],

            'is_alternative' => 'boolean',
            'is_net' => 'boolean',
            'is_free_text' => 'boolean',
        ];
    }
}
