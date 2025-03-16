<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\ContactOrigin\CreateContactOrigin;
use FluxErp\Actions\ContactOrigin\DeleteContactOrigin;
use FluxErp\Actions\ContactOrigin\UpdateContactOrigin;
use FluxErp\Livewire\DataTables\ContactOriginList;
use FluxErp\Livewire\Forms\ContactOriginForm;
use FluxErp\Models\ContactOrigin;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ContactOrigins extends ContactOriginList
{
    public ContactOriginForm $contactOriginForm;

    protected ?string $includeBefore = 'flux::livewire.settings.contact-origins';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->when(resolve_static(CreateContactOrigin::class, 'canPerformAction', [false]))
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
                ->when(resolve_static(UpdateContactOrigin::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteContactOrigin::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __(
                        'wire:confirm.delete',
                        ['model' => __('Contact Origin')]
                    ),
                ]),
        ];
    }

    #[Renderless]
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

    #[Renderless]
    public function edit(ContactOrigin $contactOrigin): void
    {
        $this->contactOriginForm->reset();
        $this->contactOriginForm->fill($contactOrigin);

        $this->js(<<<'JS'
            $modalOpen('edit-contact-origin-modal');
        JS);
    }

    #[Renderless]
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
}
