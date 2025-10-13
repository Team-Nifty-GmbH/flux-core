<?php

namespace FluxErp\Livewire\Forms;

class OrderScheduleForm extends ScheduleForm
{
    public array $cron = [
        'methods' => [
            'basic' => null,
            'dayConstraint' => null,
            'timeConstraint' => null,
        ],
        'parameters' => [
            'basic' => [null, null, null],
            'dayConstraint' => [],
            'timeConstraint' => [null, null],
            'printLayouts' => [],
            'emailTemplateId' => null,
            'autoPrintAndSend' => false,
        ],
    ];
}
