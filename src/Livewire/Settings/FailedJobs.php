<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\FailedJobList;
use FluxErp\Models\FailedJob;
use Livewire\Attributes\Renderless;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class FailedJobs extends FailedJobList
{
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

    #[Renderless]
    public function show(FailedJob $failedJob): void
    {
        $this->failedJob = $failedJob->toArray();
        $this->failedJob['exception'] = explode("\n", data_get($this->failedJob, 'exception', ''));

        $this->js(<<<'JS'
            $modalOpen('show-failed-job');
        JS);
    }

    protected function itemToArray($item): array
    {
        $array = parent::itemToArray($item);

        $array['exception'] = data_get(explode("\n", $item->exception ?? ''), 0);

        return $array;
    }
}
