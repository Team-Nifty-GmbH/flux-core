<?php

namespace FluxErp\Rulesets\PurchaseInvoice;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Media;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\User;
use FluxErp\Rules\MediaUploadType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\ContactBankConnection\BankConnectionRuleset;
use FluxErp\Rulesets\FluxRuleset;

class CreatePurchaseInvoiceRuleset extends FluxRuleset
{
    protected static ?string $model = PurchaseInvoice::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(BankConnectionRuleset::class, 'getRules'),
            resolve_static(PurchaseInvoicePositionRuleset::class, 'getRules'),
            resolve_static(TagRuleset::class, 'getRules'),
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:purchase_invoices,uuid',
            'approval_user_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => User::class])
                    ->where('is_active', true),
            ],
            'client_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'contact_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'currency_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Currency::class]),
            ],
            'lay_out_user_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => User::class])->where('is_active', true),
            ],
            'order_type_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => OrderType::class]),
            ],
            'payment_type_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => PaymentType::class])
                    ->where('is_purchase', true)
                    ->where('is_active', true),
            ],
            'invoice_date' => 'nullable|date',
            'payment_target_date' => 'nullable|date',
            'payment_discount_target_date' => 'nullable|date',
            'payment_discount_percent' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 1]),
            ],
            'system_delivery_date' => 'date|nullable|required_with:system_delivery_date_end',
            'system_delivery_date_end' => 'date|nullable|after_or_equal:system_delivery_date',
            'invoice_number' => 'nullable|string|max:255',
            'total_gross_price' => [
                'nullable',
                app(Numeric::class, ['min' => 0]),
            ],
            'is_net' => 'boolean',

            'media' => 'required',
            'media.id' => [
                'integer',
                app(ModelExists::class, ['model' => Media::class]),
            ],
            'media_type' => ['sometimes', app(MediaUploadType::class)],
        ];
    }
}
