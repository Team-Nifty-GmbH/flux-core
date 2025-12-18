<?php

namespace FluxErp\Livewire\MyEmployeeProfile;

use FluxErp\Livewire\Employee\AbsenceRequests as BaseAbsenceRequests;

class AbsenceRequests extends BaseAbsenceRequests
{
    protected static string $detailRouteName = 'human-resources.my-employee-profile.my-absence-request';
}
