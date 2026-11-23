<?php

use FluxErp\Models\Contact;
use FluxErp\Models\ProductProperty;
use FluxErp\Traits\Model\HasParentMorphClass;
use Illuminate\Database\ClassMorphViolationException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Exceptions;

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
    ]);
    Broadcast::purge('mercure');
    require flux_path('routes/channels.php');

    $this->recordChannel = class_to_broadcast_channel(Contact::class);
    $this->modelChannel = class_to_broadcast_channel(Contact::class, false) . '.';
    $this->authorize = function (string $channel): array {
        $request = Request::create('/broadcasting/auth', 'POST', ['channel_names' => [$channel]]);
        $request->setLaravelSession(new Store('test', new ArraySessionHandler(120)));
        $request->setUserResolver(fn () => $this->user);

        return Broadcast::auth($request)->getData(true)['channel_names'];
    };
});

test('model channels are not registered before a channel is authorized', function (): void {
    expect(Broadcast::driver()->getChannels())
        ->not->toHaveKey($this->recordChannel)
        ->and(Broadcast::driver()->getChannels())->not->toHaveKey($this->modelChannel)
        ->and(Broadcast::driver()->getChannels())->toHaveKey('job-batch.{id}');
});

test('authorizing a channel registers the model channels', function (): void {
    $contact = Contact::factory()->create();
    $channel = 'private-' . morph_alias(Contact::class) . '.' . $contact->getKey();

    expect(($this->authorize)($channel))->toBe([['name' => $channel]])
        ->and(Broadcast::driver()->getChannels())->toHaveKey($this->recordChannel)
        ->and(Broadcast::driver()->getChannels())->toHaveKey($this->modelChannel);
});

test('a channel registered under the name of a model channel keeps its callback', function (): void {
    $contact = Contact::factory()->create();
    $channel = 'private-' . morph_alias(Contact::class) . '.' . $contact->getKey();
    Broadcast::channel($this->recordChannel, fn (): bool => false);

    expect(($this->authorize)($channel))->toBe([['name' => $channel, 'denied' => true]]);
});

test('a subclass the morph map alias points at does not break channel authorization', function (): void {
    $subclass = get_class(new class() extends ProductProperty
    {
        use HasParentMorphClass;

        protected $table = 'product_properties';
    });
    $this->app->bind(ProductProperty::class, $subclass);
    $morphMap = Relation::morphMap();
    Relation::morphMap(['product_property' => $subclass]);

    try {
        $contact = Contact::factory()->create();
        $channel = 'private-' . morph_alias(Contact::class) . '.' . $contact->getKey();

        expect(morph_alias(ProductProperty::class))->toBe('product_property')
            ->and(($this->authorize)($channel))->toBe([['name' => $channel]])
            ->and(Broadcast::driver()->getChannels())->toHaveKey('product_property.{productProperty}');
    } finally {
        Relation::morphMap($morphMap, false);
    }
});

test('a model without a resolvable morph alias does not break channel authorization', function (): void {
    Exceptions::fake();
    $bound = get_class(new class() extends ProductProperty
    {
        use HasParentMorphClass;

        protected $table = 'product_properties';
    });
    $mapped = get_class(new class() extends ProductProperty
    {
        use HasParentMorphClass;

        protected $table = 'product_properties';
    });
    $this->app->bind(ProductProperty::class, $bound);
    $morphMap = Relation::morphMap();
    Relation::morphMap(['product_property' => $mapped]);

    try {
        $contact = Contact::factory()->create();
        $channel = 'private-' . morph_alias(Contact::class) . '.' . $contact->getKey();

        expect(($this->authorize)($channel))->toBe([['name' => $channel]])
            ->and(Broadcast::driver()->getChannels())->toHaveKey($this->recordChannel)
            ->and(Broadcast::driver()->getChannels())->not->toHaveKey('product_property.{productProperty}');
        Exceptions::assertReported(ClassMorphViolationException::class);
    } finally {
        Relation::morphMap($morphMap, false);
    }
});
