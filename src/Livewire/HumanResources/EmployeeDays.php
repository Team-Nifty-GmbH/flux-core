<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Livewire\DataTables\EmployeeDayList;
use Livewire\Attributes\Renderless;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class EmployeeDays extends EmployeeDayList
{
    protected static string $detailRouteName = 'human-resources.employee-days.show';

    public bool $hasNoRedirect = true;

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('View'))
                ->icon('eye')
                ->color('indigo')
                ->wireClick('view(record.id)'),
        ];
    }

    #[Renderless]
    public function view(?int $id = null): void
    {
        $this->redirectRoute(static::$detailRouteName, ['id' => $id], navigate: true);
    }
}
