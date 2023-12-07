<?php

namespace FluxErp\Livewire\Contacts;

use FluxErp\Actions\Contact\DeleteContact;
use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Htmlables\TabButton;
use FluxErp\Models\Contact as ContactModel;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;
use WireUi\Traits\Actions;

class Contact extends Component
{
    use Actions, WithFileUploads, WithTabs;

    public array $address;

    public array $contact;

    public array $newContact = [
        'client_id' => null,
        'address' => [
            'country_id' => null,
            'language_id' => null,
            'is_main_address' => true,
        ],
    ];

    public int $contactId = 0;

    #[Url(as: 'address')]
    public ?int $addressId = null;

    public string $searchModel;

    public bool $newContactModal = false;

    public $avatar;

    #[Url]
    public string $tab = 'contact.addresses';

    public string $search = '';

    public string $orderBy = '';

    public bool $orderAsc = true;

    public array $priceLists = [];

    public array $paymentTypes = [];

    protected function getListeners(): array
    {
        $channel = (new ContactModel())->broadcastChannel(true);

        return array_merge(['goToContactWithAddress' => 'goToContactWithAddress'], [
            'echo-private:' . $channel . '.' . $this->contactId . ',.ContactUpdated' => 'contactUpdatedEvent',
            'echo-private:' . $channel . ',.ContactDeleted' => 'contactDeletedEvent',
        ]);
    }

    public function mount(?int $id = null): void
    {
        $this->contactId = $id;
        $contact = ContactModel::query()
            ->with('addresses')
            ->when($this->contactId, fn ($query) => $query->whereKey($this->contactId))
            ->firstOrFail();

        $this->avatar = $contact->getAvatarUrl();

        $this->contact = $contact->toArray();

        $mainAddress = $contact->addresses
            ->where('is_main_address', true)
            ->first() ?:
            $contact->addresses->first();

        if (! $mainAddress) {
            abort(404);
        }

        $this->contact['main_address'] = $mainAddress->toArray();

        $this->address = $this->addressId ?
            $contact->addresses()->whereKey($this->addressId)->firstOrFail()->toArray() :
            $mainAddress->toArray();

        $this->priceLists = PriceList::query()->select(['id', 'name'])->get()->toArray();
        $this->paymentTypes = PaymentType::query()->select(['id', 'name'])->get()->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.contact.contact');
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('contact.addresses')->label(__('Addresses')),
            TabButton::make('contact.orders')->label(__('Orders')),
            TabButton::make('contact.accounting')->label(__('Accounting')),
            TabButton::make('contact.tickets')->label(__('Tickets')),
            TabButton::make('contact.statistics')->label(__('Statistics')),
        ];
    }

    public function updatedContact(): void
    {
        try {
            UpdateContact::make($this->contact)->validate()->execute();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);
        }
    }

    public function goToContactWithAddress(int $contactId, int $addressId): void
    {
        $this->contactId = $contactId;
        $this->addressId = $addressId;
        $this->mount();
    }

    public function delete(): false|RedirectResponse|Redirector
    {
        $this->skipRender();
        try {
            DeleteContact::make($this->contact)
                ->checkPermission()
                ->validate()
                ->execute();

            return redirect()->route('contacts');
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        return false;
    }

    public function changeCommissionAgent(int $id): void
    {
        try {
            UpdateContact::make([
                'id' => $this->contact['id'],
                'agent_id' => $id,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        $this->skipRender();
    }

    public function contactUpdatedEvent(array $data): void
    {
        $this->contact = $data['model'];
    }

    public function contactDeletedEvent(array $data): void
    {
        if ($data['model']['id'] === $this->contactId) {
            $this->next();
        }
    }

    public function updatedAvatar(): void
    {
        $this->collection = 'avatar';
        try {
            $response = $this->saveFileUploadsToMediaLibrary('avatar', $this->contactId, ContactModel::class);
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->avatar = $response[0]['data']->getUrl();
    }

    private function next(): Redirector
    {
        $nextId = ContactModel::query()
            ->where('id', '>', $this->contactId)
            ->first()
            ?->id;

        if (! $nextId) {
            $nextId = ContactModel::query()
                ->where('id', '<', $this->contactId)
                ->orderBy('id', 'DESC')
                ->first()
                ?->id;
        }

        return redirect(route('contacts.id?', ['id' => $nextId]));
    }
}
