<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Livewire\Forms\Contact;
use FluxErp\Models\Address;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ContactList extends DataTable
{
    protected string $view = 'flux::livewire.contact.contacts';

    protected string $model = Address::class;

    public bool $showFilterInputs = true;

    public array $enabledCols = [
        'avatar',
        'contact.customer_number',
        'is_main_address',
        'company',
        'firstname',
        'lastname',
        'street',
        'zip',
        'city',
    ];

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $aggregatable = ['*'];

    public array $availableCols = ['*'];

    public array $formatters = [
        'avatar' => 'image',
    ];

    public Contact $contact;

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.show()',
                ])
                ->when(fn() => auth()->user()->can('create', $this->model)),
        ];
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with('contact.media');
    }

    public function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['avatar'] = $item->getAvatarUrl();

        return $returnArray;
    }

    public function show(): void
    {
        $this->contact->reset();

        $this->js(
            <<<'JS'
               $openModal('new-contact');
            JS
        );
    }

    public function save(): false|RedirectResponse|Redirector
    {
        try {
            $this->contact->save();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('Contact saved'));

        return redirect(route('contacts.id?', ['id' => $this->contact->id]));
    }
}
