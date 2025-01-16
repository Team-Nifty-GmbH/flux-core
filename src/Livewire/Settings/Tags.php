<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Actions\Tag\DeleteTag;
use FluxErp\Actions\Tag\UpdateTag;
use FluxErp\Livewire\DataTables\TagList;
use FluxErp\Livewire\Forms\TagForm;
use FluxErp\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Tags\HasTags;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Tags extends TagList
{
    protected ?string $includeBefore = 'flux::livewire.settings.tags';

    public TagForm $tagForm;

    #[Locked]
    public bool $isSelectable = true;

    #[Renderless]
    public function deleteSelected(): void
    {
        $this->getSelectedModelsQuery()
            ->pluck('id')
            ->each(function (int $id) {
                try {
                    DeleteTag::make(['id' => $id])
                        ->checkPermission()
                        ->validate()
                        ->execute();
                } catch (ValidationException|UnauthorizedException $e) {
                    exception_to_notifications($e, $this);
                }
            });

        $this->loadData();

        $this->reset('selected');
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->when(resolve_static(CreateTag::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->when(resolve_static(UpdateTag::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->label(__('Delete'))
                ->color('negative')
                ->icon('trash')
                ->when(resolve_static(DeleteTag::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Tag')]),
                ]),
        ];
    }

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Delete'))
                ->color('negative')
                ->when(resolve_static(DeleteTag::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'deleteSelected',
                    'wire:flux-confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Tag')]),
                ]),
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'taggables' => model_info_all()
                    ->filter(fn ($modelInfo) => in_array(
                        HasTags::class,
                        class_uses_recursive($modelInfo->class)
                    ))
                    ->unique('morphClass')
                    ->map(fn ($modelInfo) => [
                        'label' => __(Str::headline($modelInfo->morphClass)),
                        'value' => $modelInfo->morphClass,
                    ])
                    ->toArray(),
            ]
        );
    }

    public function edit(Tag $tag): void
    {
        $this->tagForm->reset();
        $this->tagForm->fill($tag);

        $this->js(<<<'JS'
            $openModal('edit-tag-modal');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->tagForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(Tag $tag): bool
    {
        $this->tagForm->reset();
        $this->tagForm->fill($tag);

        try {
            $this->tagForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
