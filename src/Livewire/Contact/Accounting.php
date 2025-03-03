<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class Accounting extends Component
{
    use Actions, WithTabs;

    public string $tab = 'contact.accounting.general';

    #[Modelable]
    public ContactForm $contact;

    public function render(): Factory|Application|View
    {
        return view('flux::livewire.contact.accounting');
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('contact.accounting.general')
                ->text(__('General'))
                ->isLivewireComponent()
                ->wireModel('contact'),
            TabButton::make('contact.accounting.bank-connections')
                ->text(__('Bank connections'))
                ->isLivewireComponent()
                ->wireModel('contact.id'),
            TabButton::make('contact.accounting.discounts')
                ->text(__('Discounts')),
            TabButton::make('contact.accounting.sepa-mandates')
                ->text(__('Sepa Mandates'))
                ->isLivewireComponent()
                ->wireModel('contact'),
        ];
    }
}
