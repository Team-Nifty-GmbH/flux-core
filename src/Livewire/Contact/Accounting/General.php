<?php

namespace FluxErp\Livewire\Contact\Accounting;

use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Currency;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class General extends Component
{
    #[Modelable]
    public ContactForm $contact;

    public function render(): Factory|Application|View
    {
        return view(
            'flux::livewire.contact.accounting.general',
            [
                'paymentTypes' => resolve_static(PaymentType::class, 'query')
                    ->where('is_sales', true)
                    ->where('is_active', true)
                    ->orderByDesc('is_default')
                    ->get(['id', 'name'])
                    ->toArray(),
                'purchasePaymentTypes' => resolve_static(PaymentType::class, 'query')
                    ->where('is_purchase', true)
                    ->where('is_active', true)
                    ->orderByDesc('is_default')
                    ->get(['id', 'name'])
                    ->toArray(),
                'priceLists' => resolve_static(PriceList::class, 'query')
                    ->orderByDesc('is_default')
                    ->get(['id', 'name'])
                    ->toArray(),
                'currencies' => resolve_static(Currency::class, 'query')
                    ->orderByDesc('is_default')
                    ->get(['id', 'name'])
                    ->toArray(),
                'vatRates' => resolve_static(VatRate::class, 'query')
                    ->where('is_tax_exemption', true)
                    ->get(['id', 'name'])
                    ->toArray(),
            ]
        );
    }
}
