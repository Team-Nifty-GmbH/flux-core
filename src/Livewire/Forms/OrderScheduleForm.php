<?php

namespace FluxErp\Livewire\Forms;

class OrderScheduleForm extends ScheduleForm
{
    public array $parameters = [
        'printLayouts' => [],
        'emailTemplateId' => null,
        'autoPrintAndSend' => false,
    ];
}
