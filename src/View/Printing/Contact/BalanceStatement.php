<?php

namespace FluxErp\View\Printing\Contact;

use FluxErp\Models\Contact;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class BalanceStatement extends PrintableView
{
    public Contact $model;

    public function __construct(Contact $contact)
    {
        $this->model = $contact;
    }

    public static function shouldForceRecreate(): bool
    {
        return true;
    }

    public function render(): View|Factory
    {
        return view('print::contact.balance-statement', [
            'model' => $this->model,
        ]);
    }

    public function getFileName(): string
    {
        return $this->getSubject() . '_' . $this->model->customer_number;
    }

    public function getModel(): Contact
    {
        return $this->model;
    }

    public function getSubject(): string
    {
        return __('Balance Statement');
    }
}
