<?php

namespace FluxErp\View\Printing\SepaMandate;

use FluxErp\Models\SepaMandate;
use FluxErp\Models\Tenant;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class SepaMandateView extends PrintableView
{
    public SepaMandate $model;

    public function __construct(SepaMandate $sepaMandate)
    {
        $this->model = $sepaMandate;
    }

    public function render(): View|Factory
    {
        /** @var Tenant $tenant */
        $tenant = $this->model->tenant;
        $tenant->localize($this->model->mainAddress?->language_id);

        return view('print::sepa-mandate.sepa-mandate', [
            'model' => $this->model,
            'tenant' => $tenant,
        ]);
    }

    public function getFileName(): string
    {
        return $this->getSubject();
    }

    public function getModel(): SepaMandate
    {
        return $this->model;
    }

    public function getSubject(): string
    {
        return __('Sepa Mandate') . ' ' . $this->model->contact->mainAddress->name;
    }
}
