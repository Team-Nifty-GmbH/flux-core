<?php

use FluxErp\Events\BroadcastableModelEventOccurred;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    config([
        'broadcasting.default' => 'mercure',
        'broadcasting.connections.mercure' => [
            'driver' => 'mercure',
            'url' => 'http://localhost/.well-known/mercure',
            'public_url' => 'http://localhost/.well-known/mercure',
            'secret' => str_repeat('s', 32),
            'cookie_name' => 'mercureAuthorization',
        ],
        'auth.guards.address' => ['driver' => 'session', 'provider' => 'addresses'],
        'auth.providers.addresses' => ['driver' => 'eloquent', 'model' => Address::class],
    ]);
    Auth::forgetGuards();
    Broadcast::purge('mercure');
    require flux_path('routes/channels.php');
    Event::fake([BroadcastableModelEventOccurred::class]);

    $this->ownContact = Contact::factory()->create();
    $this->foreignContact = Contact::factory()->create();
    $this->portalAddress = Address::factory()->create(['contact_id' => $this->ownContact->getKey()]);

    $this->isAllowed = function (Authenticatable $user, string $guard, string $channel): bool {
        $request = Request::create('/broadcasting/auth', 'POST', ['channel_names' => ['private-' . $channel]]);
        $request->setLaravelSession(new Store('test', new ArraySessionHandler(120)));
        $request->setUserResolver(fn (?string $requested = null) => $requested === $guard ? $user : null);

        $response = Broadcast::connection('mercure')->auth($request);

        return $response instanceof JsonResponse && ! data_get($response->getData(true), 'channel_names.0.denied');
    };
});

test('a portal address joins the channel of its own contact', function (): void {
    expect(($this->isAllowed)($this->portalAddress, 'address', 'contact.' . $this->ownContact->getKey()))
        ->toBeTrue();
});

test('a portal address may not join the channel of another contact', function (): void {
    expect(($this->isAllowed)($this->portalAddress, 'address', 'contact.' . $this->foreignContact->getKey()))
        ->toBeFalse();
});

test('a portal address may only join records of its own contact', function (): void {
    $colleague = Address::factory()->create(['contact_id' => $this->ownContact->getKey()]);
    $foreignAddress = Address::factory()->create(['contact_id' => $this->foreignContact->getKey()]);

    expect(($this->isAllowed)($this->portalAddress, 'address', 'address.' . $colleague->getKey()))
        ->toBeTrue()
        ->and(($this->isAllowed)($this->portalAddress, 'address', 'address.' . $foreignAddress->getKey()))
        ->toBeFalse();
});

test('a portal address may not join records without a contact', function (): void {
    expect(($this->isAllowed)($this->portalAddress, 'address', 'tenant.' . $this->dbTenant->getKey()))
        ->toBeFalse();
});

test('a portal address joins tickets opened by addresses of its contact only', function (): void {
    $colleague = Address::factory()->create(['contact_id' => $this->ownContact->getKey()]);
    $foreignAddress = Address::factory()->create(['contact_id' => $this->foreignContact->getKey()]);
    $ownTicket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $colleague->getKey(),
    ]);
    $foreignTicket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $foreignAddress->getKey(),
    ]);

    expect(($this->isAllowed)($this->portalAddress, 'address', 'ticket.' . $ownTicket->getKey()))
        ->toBeTrue()
        ->and(($this->isAllowed)($this->portalAddress, 'address', 'ticket.' . $foreignTicket->getKey()))
        ->toBeFalse();
});

test('a web user still joins the channel of any contact', function (): void {
    expect(($this->isAllowed)($this->user, 'web', 'contact.' . $this->foreignContact->getKey()))
        ->toBeTrue();
});
