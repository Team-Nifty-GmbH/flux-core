<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\FailedJobList;
use FluxErp\Models\FailedJob;
use Illuminate\Database\Eloquent\Model;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class FailedJobs extends FailedJobList
{
    public bool $rendersForm = false;

    public ?array $failedJob = null;

    public ?string $includeBefore = 'flux::livewire.settings.failed-jobs';

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Show'))
                ->color('indigo')
                ->icon('eye')
                ->wireClick('show(record.id)'),
        ];
    }

    public function show(FailedJob $failedJob): void
    {
        $this->failedJob = $failedJob->toArray();
        $this->failedJob['exception'] = explode("\n", data_get($this->failedJob, 'exception', ''));
        $this->rendersForm = true;
        $this->islandsHaveMounted = false;
        $this->loadData(forceRender: true);

        $this->js(<<<'JS'
            $tsui.open.modal('show-failed-job');
        JS);
    }

    protected function augmentItemArray(array &$itemArray, Model $item): void
    {
        $itemArray['exception'] = data_get(explode("\n", $item->exception ?? ''), 0);
    }
}
