<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\ContactOrigin\CreateContactOrigin;
use FluxErp\Actions\ContactOrigin\DeleteContactOrigin;
use FluxErp\Actions\ContactOrigin\UpdateContactOrigin;
use FluxErp\Livewire\DataTables\ContactOriginList;
use FluxErp\Livewire\Forms\ContactOriginForm;
use FluxErp\Models\ContactOrigin;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ContactOrigins extends ContactOriginList
{
    protected ?string $includeBefore = 'flux::livewire.settings.contact-origins';

    public ContactOriginForm $contactOriginForm;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->when(resolve_static(CreateContactOrigin::class, 'canPerformAction', [false]))
                ->wireClick('edit'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->when(resolve_static(UpdateContactOrigin::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
            DataTableButton::make()
                ->label(__('Delete'))
                ->color('negative')
                ->icon('trash')
                ->when(resolve_static(DeleteContactOrigin::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.icon.error' => __(
                        'wire:confirm.delete',
                        ['model' => __('Contact Origin')]
                    ),
                ]),
        ];
    }

    public function edit(ContactOrigin $contactOrigin): void
    {
        $this->contactOriginForm->reset();
        $this->contactOriginForm->fill($contactOrigin);

        $this->js(<<<'JS'
            $openModal('edit-contact-origin');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->contactOriginForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(ContactOrigin $contactOrigin): bool
    {
        $this->contactOriginForm->reset();
        $this->contactOriginForm->fill($contactOrigin);

        try {
            $this->contactOriginForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
