<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

function searchRequestGuardRecorder(mixed ...$arguments): void
{
    SearchRequestGuardModel::$calls[] = 'function';
}

class SearchRequestGuardModel extends PaymentReminderText implements InteractsWithDataTables
{
    public static array $calls = [];

    protected $table = 'payment_reminder_texts';

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getLabel(): ?string
    {
        return 'label';
    }

    public function getUrl(): ?string
    {
        return null;
    }

    public function plainMethod(): void
    {
        static::$calls[] = 'method';
    }

    public function typedRelation(): BelongsTo
    {
        return $this->belongsTo(PaymentReminderText::class, 'id');
    }
}

beforeEach(function (): void {
    SearchRequestGuardModel::$calls = [];
    $this->user->assignRole(Role::findOrCreate('Super Admin', 'web'));
    PaymentReminderText::factory()->count(2)->create();
    $this->searchUrl = route('search', SearchRequestGuardModel::class);
});

test('a method that is no relation is not called', function (string $parameter, mixed $value): void {
    $response = $this->get($this->searchUrl . '?' . http_build_query([
        'searchFields' => ['reminder_subject'],
        $parameter => $value,
    ]));

    expect(SearchRequestGuardModel::$calls)->toBe([]);
    $response->assertUnprocessable();
})->with([
    'with' => ['with', ['plainMethod']],
    'with as string' => ['with', 'plainMethod'],
    'nested with' => ['with', ['typedRelation.plainMethod']],
    'whereHas' => ['whereHas', ['plainMethod']],
    'whereDoesntHave' => ['whereDoesntHave', 'plainMethod'],
    'whereRelation' => ['whereRelation', ['plainMethod', 'id', '>', 0]],
    'whereDoesntHaveRelation' => ['whereDoesntHaveRelation', ['plainMethod', 'id', '>', 0]],
    'doesntHave' => ['doesntHave', ['plainMethod']],
    'fields' => ['fields', ['plainMethod']],
    'appends' => ['appends', ['plainMethod']],
]);

test('a dotted search field with a method that is no relation is not called', function (): void {
    $response = $this->get($this->searchUrl . '?' . http_build_query([
        'search' => 'x',
        'searchFields' => ['plainMethod.id'],
    ]));

    expect(SearchRequestGuardModel::$calls)->toBe([]);
    $response->assertUnprocessable();
});

test('a with entry with a string key is rejected and its value not called', function (): void {
    $response = $this->get($this->searchUrl . '?' . http_build_query([
        'searchFields' => ['reminder_subject'],
        'with' => ['typedRelation' => 'searchRequestGuardRecorder'],
    ]));

    expect(SearchRequestGuardModel::$calls)->toBe([]);
    $response->assertUnprocessable();
});

test('a class that is no model is not found', function (): void {
    $this->get(route('search', 'ArrayObject') . '?searchFields[]=id')
        ->assertNotFound();
});

test('malformed query arguments are rejected', function (string $parameter, mixed $value): void {
    $this->get($this->searchUrl . '?' . http_build_query([
        'searchFields' => ['reminder_subject'],
        $parameter => $value,
    ]))
        ->assertUnprocessable();
})->with([
    'whereIn boolean' => ['whereIn', [['id', [-1], 'nope']]],
    'whereNotIn boolean' => ['whereNotIn', [['id', [-1], 'nope']]],
    'whereNull boolean' => ['whereNull', ['id', 'nope']],
    'whereNotNull boolean' => ['whereNotNull', ['id', 'nope']],
    'whereBetween boolean' => ['whereBetween', ['id', [1, 2], 'nope']],
    'whereDate boolean' => ['whereDate', ['created_at', '>', '2000-01-01', 'nope']],
    'doesntHave boolean' => ['doesntHave', ['typedRelation', 'nope']],
    'named argument' => ['whereNotNull', ['boolean' => 'nope', 'columns' => 'id']],
    'too many where arguments' => ['where', [['id', '=', 1, 'nope']]],
    'too many nested where arguments' => ['whereRelation', ['typedRelation', [['id', '=', 1, 'nope']]]],
]);

test('a hidden column cannot be searched, filtered or sorted', function (array $query): void {
    $this->get(route('search', User::class) . '?' . http_build_query($query))
        ->assertForbidden();
})->with([
    'searchFields' => [['search' => '$', 'searchFields' => ['name', 'password']]],
    'searchFields in other case' => [['search' => '$', 'searchFields' => ['PASSWORD']]],
    'where' => [['where' => [['password', 'like', '$%']]]],
    'where keyed' => [['where' => ['password' => 'x']]],
    'where qualified' => [['where' => [['users.password', 'like', '$%']]]],
    'whereNotNull' => [['whereNotNull' => ['password']]],
    'whereIn' => [['whereIn' => [['password', ['x']]]]],
    'orderBy' => [['orderBy' => 'password']],
    'option-value' => [['selected' => 'x', 'option-value' => 'password']],
]);

test('a hidden column of a related model cannot be searched', function (): void {
    $this->get(route('search', Order::class) . '?' . http_build_query([
        'search' => '$',
        'searchFields' => ['agent.password'],
    ]))
        ->assertForbidden();
});

test('a relation with a union return type keeps working', function (): void {
    $this->get(route('search', Contact::class) . '?' . http_build_query([
        'searchFields' => ['customer_number'],
        'with' => ['categories', 'category'],
    ]))
        ->assertOk();
});

test('real relations, attributes and accessors keep working', function (): void {
    $contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $response = $this->get(route('search', Address::class) . '?' . http_build_query([
        'search' => $address->name,
        'searchFields' => ['name', 'contact.customer_number'],
        'with' => ['contact.media', 'country:id,name'],
        'whereHas' => ['contact'],
        'whereRelation' => ['contact', 'id', '=', $contact->getKey()],
        'whereDoesntHave' => 'addressTypes',
        'where' => [['is_main_address', '=', true]],
        'whereIn' => [['id', [$address->getKey()], 'and']],
        'whereNull' => ['deleted_at', 'or'],
        'orderBy' => 'name',
        'fields' => ['name', 'contact', 'does_not_exist'],
    ]));

    $response->assertOk()
        ->assertJsonFragment(['id' => $address->getKey()]);

    expect(data_get($response->json(), '0.contact.id'))->toBe($contact->getKey());
});
