<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\FailedJobList;
use FluxErp\Models\FailedJob;
use Livewire\Attributes\Renderless;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class FailedJobs extends FailedJobList
{
    public ?string $includeBefore = 'flux::livewire.settings.failed-jobs';

    public ?array $failedJob = null;

    #[Renderless]
    public function show(FailedJob $failedJob): void
    {
        $this->failedJob = $failedJob->toArray();
        $this->failedJob['exception'] = explode("\n", data_get($this->failedJob, 'exception', ''));

        $this->js(<<<'JS'
            $openModal('show-failed-job');
        JS);
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Show'))
                ->color('primary')
                ->icon('eye')
                ->wireClick('show(record.id)'),
        ];
    }

    public function itemToArray($item): array
    {
        $array = parent::itemToArray($item);

        $array['exception'] = data_get(explode("\n", $item->exception), 0);

        return $array;
    }
}
