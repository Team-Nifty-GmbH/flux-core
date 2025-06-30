<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Contact;
use FluxErp\Traits\Livewire\DataTable\AllowRecordMerging;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ContactList extends BaseDataTable
{
    use AllowRecordMerging;

    public ContactForm $contact;

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

    protected ?string $includeBefore = 'flux::livewire.contact.contact-list';

    protected string $model = Contact::class;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$modalOpen(\'create-contact-modal\')',
                ])
                ->when(fn () => resolve_static(CreateContact::class, 'canPerformAction', [false])),
        ];
    }

    #[Renderless]
    public function resetForm(): void
    {
        $this->contact->reset();
    }

    #[Renderless]
    public function restore(int $id): void
    {
        $this->contact->fill(
            resolve_static(Contact::class, 'query')
                ->withTrashed()
                ->whereKey($id)
                ->first()
        );

        try {
            $this->contact->restore();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->toast()
            ->success(__(':model restored', ['model' => __('Contact')]))
            ->send();
        $this->loadData();
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->contact->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->toast()
            ->success(__(':model saved', ['model' => __('Contact')]))
            ->send();

        $this->redirectRoute('contacts.id?', ['id' => $this->contact->id], navigate: true);

        return true;
    }

    protected function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['avatar'] = $item->getAvatarUrl();

        return $returnArray;
    }
}
