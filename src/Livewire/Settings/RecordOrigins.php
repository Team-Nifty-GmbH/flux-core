<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\RecordOrigin\CreateRecordOrigin;
use FluxErp\Actions\RecordOrigin\DeleteRecordOrigin;
use FluxErp\Actions\RecordOrigin\UpdateRecordOrigin;
use FluxErp\Livewire\DataTables\RecordOriginList;
use FluxErp\Livewire\Forms\RecordOriginForm;
use FluxErp\Models\RecordOrigin;
use FluxErp\Traits\HasOrigin;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class RecordOrigins extends RecordOriginList
{
    public RecordOriginForm $recordOriginForm;

    protected ?string $includeBefore = 'flux::livewire.settings.record-origins';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->when(resolve_static(CreateRecordOrigin::class, 'canPerformAction', [false]))
                ->wireClick('edit'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdateRecordOrigin::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteRecordOrigin::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __(
                        'wire:confirm.delete',
                        ['model' => __('Record Origin')]
                    ),
                ]),
        ];
    }

    #[Renderless]
    public function delete(RecordOrigin $recordOrigin): bool
    {
        $this->recordOriginForm->reset();
        $this->recordOriginForm->fill($recordOrigin);

        try {
            $this->recordOriginForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function edit(RecordOrigin $recordOrigin): void
    {
        $this->recordOriginForm->reset();
        $this->recordOriginForm->fill($recordOrigin);
        $this->js(" \$modalOpen('edit-record-origin-modal') ");
    }

    public function get_models_using_trait(string $trait, ?callable $mapFn = null): array
    {
        return model_info_all()
            ->filter(fn (ModelInfo $modelInfo) => in_array(
                $trait,
                class_uses_recursive($modelInfo->class)
            ))
            ->map($mapFn ?? fn ($modelInfo) => [
                'label' => __(class_basename($modelInfo->class)),
                'id' => morph_alias($modelInfo->class),
            ])
            ->values()
            ->toArray();
    }

    public function getOriginTypeOptionsProperty(): array
    {
        return $this->get_models_using_trait(HasOrigin::class);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->recordOriginForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
