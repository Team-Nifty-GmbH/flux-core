<?php

namespace FluxErp\Rulesets\PaymentType;

use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreatePaymentTypeRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentType::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:payment_types,uuid',
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
            'name' => 'required|string',
            'description' => 'string|nullable',
            'payment_reminder_days_1' => 'integer|nullable',
            'payment_reminder_days_2' => 'integer|nullable',
            'payment_reminder_days_3' => 'integer|nullable',
            'payment_target' => 'integer|nullable',
            'payment_discount_target' => 'integer|nullable|lte:payment_target',
            'payment_discount_percentage' => [
                'nullable',
                new Numeric(0, 1),
            ],
            'payment_reminder_text' => 'string|nullable',
            'payment_reminder_email_text' => 'string|nullable',
            'is_active' => 'boolean',
            'is_direct_debit' => 'boolean',
            'is_default' => 'boolean',
            'is_purchase' => 'boolean',
            'is_sales' => 'boolean',
            'requires_manual_transfer' => 'boolean',
        ];
    }
}
