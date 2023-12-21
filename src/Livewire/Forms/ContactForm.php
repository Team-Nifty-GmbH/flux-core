<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Address\UpdateAddress;
use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Models\Client;
use FluxErp\Models\Language;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;
use Livewire\Form;

class ContactForm extends Form
{
    #[Locked]
    public ?int $id = null;

    public ?int $client_id = null;

    public ?int $countryId = null;

    public ?int $language_id = null;

    public ?string $company = null;

    public ?string $title = null;

    public ?string $salutation = null;

    public ?string $firstname = null;

    public ?string $lastname = null;

    public ?string $zip = null;

    public ?string $city = null;

    public ?string $street = null;

    public function save(): void
    {
        $contact = Arr::only($this->toArray(), ['id', 'client_id']);
        $action = $this->id ? UpdateContact::make($contact) : CreateContact::make($contact);
        $response = $action->checkPermission()
            ->validate()
            ->execute();

        $address = array_merge($this->toArray(), ['contact_id' => $response->id]);
        if ($action instanceof UpdateContact) {
            $addressId = $response->addresses()->where('is_main_address', true)->first()?->id;
            $address = array_merge($address, ['id' => $addressId]);
        }

        $addressAction = $action instanceof UpdateContact
            ? UpdateAddress::make($address)
            : CreateAddress::make($address);

        $addressResponse = $addressAction
            ->validate()
            ->execute();

        $this->fill(array_merge($addressResponse->toArray(), $response->toArray()));
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);

        $this->client_id = Client::query()->where('is_active', true)->count() === 1
            ? Client::query()->where('is_active', true)->first()->id
            : null;
        $this->language_id = Language::query()->count() === 1 ? Language::query()->first()->id : null;
    }
}
