<?php

namespace FluxErp\View\Printing\Communication;

use FluxErp\Models\Communication;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class CommunicationView extends PrintableView
{
    public Communication $model;

    public function __construct(Communication $communication)
    {
        $this->model = $communication;
    }

    public function getModel(): Communication
    {
        return $this->model;
    }

    public function render(): View|Factory
    {
        return view('print::communication.communication', [
            'model' => $this->model,
        ]);
    }

    public function getFileName(): string
    {
        return $this->getSubject();
    }

    public function getSubject(): string
    {
        return $this->model->subject ?: __($this->model->communication_type_enum->name)
            .($this->model->date ? ' '.__('from').' '.$this->model->date->format('lll') : '');
    }
}
