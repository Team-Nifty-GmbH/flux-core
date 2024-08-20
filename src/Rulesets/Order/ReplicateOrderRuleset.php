<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ReplicateOrderRuleset extends FluxRuleset
{
    protected static ?string $model = Order::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'contact_id' => [
                'required_without:address_invoice_id',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Contact::class]),
                app(ExistsWithForeign::class, ['foreignAttribute' => 'client_id', 'table' => 'contacts']),
            ],
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            resolve_static(UpdateOrderRuleset::class, 'getRules'),
            parent::getRules(),
            resolve_static(OrderPositionRuleset::class, 'getRules')
        );
    }
}
