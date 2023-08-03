<?php

namespace FluxErp\Http\Livewire\Contacts;

use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Http\Requests\CreateAddressRequest;
use FluxErp\Http\Requests\CreateContactRequest;
use FluxErp\Http\Requests\UpdateContactRequest;
use FluxErp\Models\Address;
use FluxErp\Models\Contact as ContactModel;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Services\AddressService;
use FluxErp\Services\ContactService;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use WireUi\Traits\Actions;

class Contact extends Component
{
    use Actions, WithFileUploads;

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

    public int $addressId = 0;

    public string $searchModel;

    public bool $newContactModal = false;

    public $avatar;

    public string $tab = 'addresses';

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

    protected function rules(): array
    {
        $service = $this->address === [] ? new UpdateContactRequest() : new CreateContactRequest();

        $rules = array_merge(
            Arr::prependKeysWith($service->rules(), 'newContact.'),
            Arr::prependKeysWith((new CreateAddressRequest())->rules(), 'newContact.address.')
        );

        unset($rules['newContact.address.client_id']);
        unset($rules['newContact.address.contact_id']);

        return $rules;
    }

    public function mount(int $id = null): void
    {
        $this->contactId = $id;
        $contact = ContactModel::query()
            ->with('addresses')
            ->when($this->contactId, fn ($query) => $query->whereKey($this->contactId))
            ->firstOrFail();

        $contact->addresses->map(function (Address $address) {
            return $address->append('name');
        });

        $contact->main_address = $contact->addresses
            ->where('is_main_address', true)
            ->first()
            ->toArray();

        $this->avatar = $contact->getAvatarUrl();

        $this->contact = $contact->toArray();

        $this->address = $this->addressId ?
            $contact->addresses->whereKey($this->addressId)->firstOrFail()->toArray() :
            $contact->addresses->where('is_main_address', true)->first()->toArray();

        $this->priceLists = PriceList::query()->select(['id', 'name'])->get()->toArray();
        $this->paymentTypes = PaymentType::query()->select(['id', 'name'])->get()->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.contact.contact', [
            'tabs' => [
                'addresses' => __('Addresses'),
                'orders' => __('Orders'),
                'accounting' => __('Accounting'),
                'tickets' => __('Tickets'),
                'statistics' => __('Statistics'),
            ],
        ]);
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

    /**
     * @return Application|\Illuminate\Http\RedirectResponse|Redirector|void
     */
    public function save()
    {
        $validated = $this->validate()['newContact'];

        $function = ($this->newContact['address']['id'] ?? false) ? 'update' : 'create';
        if (! Auth::user()->can($function === 'update' ? 'api.contacts.put' : 'api.contacts.post')) {
            $this->notification()->error(__('You dont have the permission to do that.'));
        }

        $address = $validated['address'];
        unset($validated['address']);

        $contact = $validated;

        $contactService = new ContactService();

        $response = $contactService->$function($contact);
        if ($response['status'] >= 400) {
            $this->notification()->error(
                title: __('Contact could not be saved'),
                description: implode(', ', Arr::flatten($response['errors']))
            );
        } else {
            $address['contact_id'] = $response->id;
            $address['client_id'] = $response->client_id;
            (new AddressService())->create($address);

            $this->newContactModal = false;
            $this->notification()->success(__('Contact saved'));

            return redirect(route('contacts.id?', ['id' => $response->id]));
        }
    }

    /**
     * @return Application|\Illuminate\Http\RedirectResponse|Redirector|void
     */
    public function delete()
    {
        if (! user_can('api.contacts.{id}.delete')) {
            $this->notification()->error(__('You dont have the permission to do that.'));

            return;
        }

        $contactService = new ContactService();
        $contactService->delete($this->contact['id']);

        return redirect(route('contacts'));
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

    public function updatedTab(): void
    {
        if ($this->tab === 'orders') {
            $this->contact['orders'] = Order::query()
                ->where('contact_id', $this->contactId)
                ->get()
                ->toArray();
        }
    }

    public function updatedAvatar(): void
    {
        $this->collection = 'avatar';
        $response = $this->saveFileUploadsToMediaLibrary('avatar', $this->contactId, ContactModel::class);
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
