<?php

namespace FluxErp\Livewire\Forms;

class OrderScheduleForm extends ScheduleForm
{
    public array $parameters = [
        'printLayouts' => [],
        'emailTemplateId' => null,
        'autoPrint' => false,
        'autoSend' => false,
        'cancellationNoticeValue' => null,
        'cancellationNoticeUnit' => null,
        'minimumDurationValue' => null,
        'minimumDurationUnit' => null,
    ];
}
