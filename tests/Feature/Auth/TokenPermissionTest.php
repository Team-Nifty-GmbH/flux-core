<?php

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\Media\DownloadMedia;
use FluxErp\Actions\Printer\CreatePrinter;
use FluxErp\Actions\Printer\DeletePrinter;
use FluxErp\Actions\Printer\GeneratePrinterBridgeConfig;
use FluxErp\Actions\Printer\UpdatePrinter;
use FluxErp\Actions\Printing;
use FluxErp\Actions\PrintJob\DeletePrintJob;
use FluxErp\Actions\PrintJob\UpdatePrintJob;
use FluxErp\Actions\Token\CreateToken;
use FluxErp\Console\Commands\Init\InitPermissions;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Token;
use FluxErp\View\Printing\Contact\BalanceStatement;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

function createApiToken(array $abilities, array $permissions = []): Token
{
    $token = Token::query()->create(['name' => 'integration']);
    $token->givePermissionTo(
        array_map(fn (string $name): Permission => Permission::findOrCreate($name, 'token'), $permissions)
    );
    $token->token = $token->createToken('integration', $abilities)->plainTextToken;

    return $token;
}

function forgetPermission(string $name, string $guard): void
{
    Permission::query()->where('name', $name)->where('guard_name', $guard)->delete();
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

test('a token may not issue tokens without the action permission', function (): void {
    forgetPermission('action.' . CreateToken::name(), 'token');
    $token = createApiToken(['interface']);
    Auth::forgetGuards();

    $this->withToken($token->token)
        ->postJson('/api/tokens', ['name' => 'escalated', 'abilities' => ['*']])
        ->assertForbidden();

    expect(Token::query()->where('name', 'escalated')->exists())->toBeFalse();
});

test('a token may issue tokens when it holds the action permission', function (): void {
    $token = createApiToken(['interface'], ['action.' . CreateToken::name()]);
    Auth::forgetGuards();

    $this->withToken($token->token)
        ->postJson('/api/tokens', ['name' => 'issued', 'abilities' => ['interface']])
        ->assertSuccessful();

    expect(Token::query()->where('name', 'issued')->exists())->toBeTrue();
});

test('a token is denied an action whose permission was never created', function (): void {
    forgetPermission('action.' . CreateContact::name(), 'token');
    Auth::setUser(createApiToken(['*']));

    expect(CreateContact::canPerformAction(false))->toBeFalse();
});

test('a token only sees the print views it holds', function (): void {
    $permission = print_view_to_permission('balance-statement', morph_alias(Contact::class));
    forgetPermission($permission, 'token');
    Auth::setUser($token = createApiToken(['*']));

    expect(app(Contact::class)->resolvePrintViews())->not->toHaveKey('balance-statement');

    $token->givePermissionTo(Permission::findOrCreate($permission, 'token'));

    expect(app(Contact::class)->resolvePrintViews())->toHaveKey('balance-statement');
});

test('a token only sees the registered print views it holds', function (): void {
    Contact::registerPrintView('registered-statement', BalanceStatement::class);
    $permission = print_view_to_permission('registered-statement', morph_alias(Contact::class));
    forgetPermission($permission, 'token');
    Auth::setUser($token = createApiToken(['*']));

    try {
        expect(app(Contact::class)->resolvePrintViews())->not->toHaveKey('registered-statement');

        $token->givePermissionTo(Permission::findOrCreate($permission, 'token'));

        expect(app(Contact::class)->resolvePrintViews())->toHaveKey('registered-statement');
    } finally {
        unset(Contact::$registeredPrintViews['registered-statement']);
    }
});

test('a token is forbidden to print a view it does not hold', function (): void {
    $contact = Contact::factory()->create();
    $permission = print_view_to_permission('balance-statement', morph_alias(Contact::class));
    Permission::findOrCreate($permission, 'token');
    $token = createApiToken(['*'], ['action.' . Printing::name()]);
    Auth::forgetGuards();

    $this->withToken($token->token)
        ->postJson('/api/print/pdf', [
            'model_type' => morph_alias(Contact::class),
            'model_id' => $contact->getKey(),
            'view' => 'balance-statement',
        ])
        ->assertForbidden();
});

test('printing a view the model does not offer is a validation error', function (): void {
    $contact = Contact::factory()->create();
    $token = createApiToken(['*'], ['action.' . Printing::name()]);
    Auth::forgetGuards();

    $this->withToken($token->token)
        ->postJson('/api/print/pdf', [
            'model_type' => morph_alias(Contact::class),
            'model_id' => $contact->getKey(),
            'view' => 'not-a-view',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('view');
});

test('init permissions creates action and print permissions for the token guard', function (): void {
    Contact::registerPrintView('registered-statement', BalanceStatement::class);

    try {
        Artisan::call(InitPermissions::class);
    } finally {
        unset(Contact::$registeredPrintViews['registered-statement']);
    }

    $guards = fn (string $name): array => Permission::query()
        ->where('name', $name)
        ->orderBy('guard_name')
        ->pluck('guard_name')
        ->all();

    expect($guards('action.order.create'))->toBe(['sanctum', 'token', 'web'])
        ->and($guards(print_view_to_permission('balance-statement', morph_alias(Contact::class))))
        ->toBe(['token', 'web'])
        ->and($guards(print_view_to_permission('registered-statement', morph_alias(Contact::class))))
        ->toBe(['token', 'web']);
});

test('the printer bridge token may run every action the bridge calls', function (): void {
    Artisan::call(InitPermissions::class);
    $this->user->givePermissionTo(Permission::findByName('action.' . CreateToken::name(), 'web'));
    Auth::login($this->user);

    GeneratePrinterBridgeConfig::make([
        'instance_name' => 'bridge',
        'printer_check_interval' => 5,
        'job_check_interval' => 5,
        'api_port' => 8080,
        'reverb_disabled' => true,
        'force_regenerate' => false,
    ])
        ->validate()
        ->execute();

    Auth::setUser(Token::query()->where('name', 'bridge')->sole());

    expect(CreatePrinter::canPerformAction(false))->toBeTrue()
        ->and(UpdatePrinter::canPerformAction(false))->toBeTrue()
        ->and(DeletePrinter::canPerformAction(false))->toBeTrue()
        ->and(UpdatePrintJob::canPerformAction(false))->toBeTrue()
        ->and(DownloadMedia::canPerformAction(false))->toBeTrue()
        ->and(DeletePrintJob::canPerformAction(false))->toBeFalse()
        ->and(Auth::user()->hasPermissionTo('api.print-jobs.{id}.get'))->toBeTrue();
});
