<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Models\Address;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class AddressList extends BaseDataTable
{
    protected ?string $includeBefore = 'flux::livewire.contact.contacts';

    protected string $model = Address::class;

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

    public array $formatters = [
        'avatar' => 'image',
    ];

    public bool $showMap = false;

    public ContactForm $contact;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Show on Map'))
                ->color('primary')
                ->icon('globe')
                ->wireClick('$toggle(\'showMap\', true)'),
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.show()',
                ])
                ->when(fn () => resolve_static(CreateContact::class, 'canPerformAction', [false])),
        ];
    }

    protected function getBuilder(Builder $builder): Builder
    {
        // add contact_id to the select statement to ensure that the contact route is available
        return $builder->addSelect('contact_id')->with('contact.media');
    }

    protected function getReturnKeys(): array
    {
        return array_merge(parent::getReturnKeys(), ['contact_id']);
    }

    protected function itemToArray($item): array
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
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('Contact saved'));

        return redirect(route('contacts.id?', ['id' => $this->contact->id]));
    }

    #[Renderless]
    public function loadData(): void
    {
        parent::loadData();

        if ($this->showMap) {
            $this->updatedShowMap();
        }
    }

    #[Renderless]
    public function updatedShowMap(): void
    {
        $this->dispatch('load-map');
    }

    #[Renderless]
    public function loadMap(): array
    {
        return $this->buildSearch()
            ->limit(100)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('is_main_address', true)
            ->select([
                'id',
                'contact_id',
                'latitude',
                'longitude',
                'company',
                'firstname',
                'lastname',
                'street',
                'zip',
                'city',
            ])
            ->with([
                'contact:id',
                'contact.media' => fn ($query) => $query->where('collection_name', 'avatar'),
            ])
            ->get()
            ->toMap()
            ->toArray();
    }
}
