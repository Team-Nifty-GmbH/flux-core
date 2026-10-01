<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\BlockedFile\DeleteBlockedFile;
use FluxErp\Livewire\DataTables\BlockedFileList;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class BlockedFiles extends BlockedFileList
{
    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Unblock'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteBlockedFile::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('Unblock this file? It can be stored again afterwards.'),
                ]),
        ];
    }

    #[Renderless]
    public function delete(int $id): bool
    {
        try {
            DeleteBlockedFile::make(['id' => $id])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
