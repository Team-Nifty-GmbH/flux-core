<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Contact;
use FluxErp\Models\User;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTable\AllowRecordMerging;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ContactList extends BaseDataTable
{
    use AllowRecordMerging, DataTableHasFormEdit;

    public array $enabledCols = [
        'avatar',
        'customer_number',
        'main_address.company',
        'main_address.firstname',
        'main_address.lastname',
        'main_address.street',
        'main_address.zip',
        'main_address.city',
    ];

    public array $formatters = [
        'avatar' => 'image',
    ];

    public bool $isSelectable = true;

    public ?int $agentId = null;

    #[DataTableForm(
        only: [
            'tenant_id',
            'company',
            'salutation',
            'title',
            'firstname',
            'lastname',
            'street',
            'zip',
            'city',
            'country_id',
            'language_id',
            'email_primary',
            'phone',
            'phone_mobile',
            'record_origin_id',
        ],
    )]
    public ContactForm $createContactForm;

    protected ?string $includeBefore = 'flux::livewire.contact.contact-list';

    protected string $model = Contact::class;

    protected function getTableActions(): array
    {
        return array_merge(
            $this->getTableActionsDataTableHasFormEdit(),
            []
        );
    }

    protected function getSelectedActions(): array
    {
        return array_merge(
            parent::getSelectedActions(),
            [
                DataTableButton::make()
                    ->text(__('Assign to agent'))
                    ->when(fn () => resolve_static(UpdateContact::class, 'canPerformAction', [false]))
                    ->xOnClick(<<<'JS'
                        $modalOpen('assign-agent-modal');
                    JS),
            ]
        );
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->createContactForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->redirectRoute('contacts.id?', ['id' => $this->createContactForm->id], navigate: true);

        return true;
    }

    #[Renderless]
    public function assignToAgent(): bool
    {
        $actions = [];

        try {
            if (! resolve_static(User::class, 'query')
                ->where('is_active', true)
                ->whereKey($this->agentId)
                ->exists()
            ) {
                throw ValidationException::withMessages(['agentId' => __('The selected agent does not exist.')]);
            }

            foreach ($this->getSelectedModelsQuery()->pluck('id') as $contactId) {
                $actions[] = UpdateContact::make([
                    'id' => $contactId,
                    'agent_id' => $this->agentId,
                ])
                    ->checkPermission()
                    ->validate();
            }
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        foreach ($actions as $action) {
            $action->execute();
        }

        $this->loadData();
        $this->toast()
            ->success(__('Contacts assigned to agent successfully.'))
            ->send();

        return true;
    }

    protected function getRowActionEditButton(): ?DataTableButton
    {
        return null;
    }

    protected function supportRestore(): bool
    {
        return true;
    }

    protected function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['avatar'] = $item->getAvatarUrl();

        return $returnArray;
    }
}
