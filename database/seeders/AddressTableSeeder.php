<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Country;
use FluxErp\Models\Language;
use Illuminate\Database\Seeder;

class AddressTableSeeder extends Seeder
{
    public function run()
    {
        $clients = Client::all();
        $languages = Language::all();
        $countries = Country::all();

        foreach ($clients as $client) {
            $contacts = Contact::query()
                ->where('client_id', $client->id)
                ->get();

            foreach ($contacts as $contact) {
                Address::factory()->create([
                    'client_id' => $client->id,
                    'contact_id' => $contact->id,
                    'is_main_address' => true,
                ]);

                $addressTypeInvoice = AddressType::query()
                    ->where('address_type_code', 'inv')
                    ->where('client_id', $client->id)
                    ->first();
                $addressTypeDelivery = AddressType::query()
                    ->where('address_type_code', 'del')
                    ->where('client_id', $client->id)
                    ->first();

                for ($i = 0; $i < 2; $i++) {
                    $address = Address::factory()->create([
                        'client_id' => $client->id,
                        'contact_id' => $contact->id,
                        'language_id' => $languages->random()->id,
                        'country_id' => $countries->random()->id,
                        'is_main_address' => false,
                    ]);

                    if ($i === 0) {
                        $address->addressTypes()->attach([
                            $addressTypeInvoice->id,
                            $addressTypeDelivery->id,
                        ]);
                    }
                }
            }
        }
    }
}
