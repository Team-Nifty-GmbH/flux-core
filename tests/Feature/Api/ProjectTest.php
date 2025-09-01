<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use Carbon\Carbon;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Project;
use FluxErp\States\Project\Done;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $addresses = Address::factory()->count(2)->create([
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $this->contact->id,
    ]);

    $priceList = PriceList::factory()->create();

    $currency = Currency::factory()->create([
        'is_default' => true,
    ]);

    $language = Language::factory()->create();

    $orderType = OrderType::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create();

    $this->order = Order::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'language_id' => $language->id,
        'order_type_id' => $orderType->id,
        'payment_type_id' => $paymentType->id,
        'price_list_id' => $priceList->id,
        'currency_id' => $currency->id,
        'address_invoice_id' => $addresses[0]->id,
        'address_delivery_id' => $addresses[1]->id,
        'is_locked' => false,
    ]);

    $this->projects = Project::factory()->count(2)->create([
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $this->contact->id,
        'order_id' => $this->order->id,
        'responsible_user_id' => $this->user->id,
    ]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.projects.{id}.get'),
        'index' => Permission::findOrCreate('api.projects.get'),
        'create' => Permission::findOrCreate('api.projects.post'),
        'update' => Permission::findOrCreate('api.projects.put'),
        'delete' => Permission::findOrCreate('api.projects.{id}.delete'),
        'finish' => Permission::findOrCreate('api.projects.finish.post'),
        'statistics' => Permission::findOrCreate('api.projects.{id}.statistics.get'),
    ];
});

test('create project', function (): void {
    $project = [
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $this->contact->id,
        'order_id' => $this->order->id,
        'responsible_user_id' => $this->user->id,
        'parent_id' => $this->projects[0]->id,
        'project_number' => Str::random(),
        'name' => Str::random(),
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-t'),
        'description' => 'New description text for further information',
        'time_budget' => '6:40',
        'budget' => rand(1, 100000) / 100,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/projects', $project);
    $response->assertStatus(201);

    $responseProject = json_decode($response->getContent())->data;
    $dbProject = Project::query()
        ->whereKey($responseProject->id)
        ->first();
    expect($dbProject)->not->toBeEmpty();
    expect($dbProject->client_id)->toEqual($project['client_id']);
    expect($dbProject->contact_id)->toEqual($project['contact_id']);
    expect($dbProject->order_id)->toEqual($project['order_id']);
    expect($dbProject->responsible_user_id)->toEqual($project['responsible_user_id']);
    expect($dbProject->parent_id)->toEqual($project['parent_id']);
    expect($dbProject->project_number)->toEqual($project['project_number']);
    expect($dbProject->name)->toEqual($project['name']);
    expect(Carbon::parse($dbProject->start_date)->toDateString())->toEqual($project['start_date']);
    expect(Carbon::parse($dbProject->end_date)->toDateString())->toEqual($project['end_date']);
    expect($dbProject->description)->toEqual($project['description']);
    expect($dbProject->time_budget)->toEqual($project['time_budget']);
    expect($dbProject->budget)->toEqual($project['budget']);
    expect($this->user->is($dbProject->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbProject->getUpdatedBy()))->toBeTrue();
});

test('create project contact not found', function (): void {
    $project = [
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => ++$this->contact->id,
        'name' => 'Project Name',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/projects', $project);
    $response->assertStatus(422);
});

test('create project order not found', function (): void {
    $project = [
        'client_id' => $this->dbClient->getKey(),
        'parent_id' => $this->order->id + 1000,
        'name' => 'Project Name',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/projects', $project);
    $response->assertStatus(422);
});

test('create project parent project not found', function (): void {
    $project = [
        'client_id' => $this->dbClient->getKey(),
        'parent_id' => ++$this->projects[1]->id,
        'name' => 'Project Name',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/projects', $project);
    $response->assertStatus(422);
    $response->assertJsonValidationErrorFor('parent_id');
});

test('create project responsible user not found', function (): void {
    $project = [
        'client_id' => $this->dbClient->getKey(),
        'responsible_user_id' => ++$this->user->id,
        'name' => 'Project Name',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/projects', $project);
    $response->assertStatus(422);
    $response->assertJsonValidationErrorFor('responsible_user_id');
});

test('create project validation fails', function (): void {
    $project = [
        'parent_id' => $this->projects[0]->id,
        'contact_id' => $this->contact->id,
        'order_id' => $this->order->id,
        'responsible_user_id' => $this->user->id,
        'project_number' => Str::random(),
        'name' => Str::random(),
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-t'),
        'description' => 'New description text for further information',
        'time_budget' => 6,
        'budget' => rand(1, 100000) / 100,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/projects', $project);
    $response->assertStatus(422);
});

test('delete project', function (): void {
    AdditionalColumn::factory()->create([
        'model_type' => Project::class,
    ]);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/projects/' . $this->projects[1]->id);
    $response->assertStatus(204);

    $project = $this->projects[1]->fresh();
    expect($project->deleted_at)->not->toBeNull();
    expect($this->user->is($project->getDeletedBy()))->toBeTrue();
});

test('delete project project has children', function (): void {
    $this->projects[0]->parent_id = $this->projects[1]->id;
    $this->projects[0]->save();

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/projects/' . $this->projects[1]->id);
    $response->assertStatus(423);
});

test('delete project project not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/projects/' . ++$this->projects[1]->id);
    $response->assertStatus(404);
});

test('finish project', function (): void {
    AdditionalColumn::factory()->create([
        'model_type' => Project::class,
    ]);

    $project = [
        'id' => $this->projects[1]->id,
        'finish' => true,
    ];

    $this->user->givePermissionTo($this->permissions['finish']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/projects/finish', $project);
    $response->assertStatus(200);

    $responseProject = json_decode($response->getContent())->data;
    $dbProject = Project::query()
        ->whereKey($responseProject->id)
        ->first();
    expect($dbProject)->not->toBeEmpty();
    expect($dbProject->id)->toEqual($project['id']);
    expect(get_class($dbProject->state))->toEqual(Done::class);
});

test('finish project project not found', function (): void {
    $project = [
        'id' => ++$this->projects[1]->id,
        'finish' => true,
    ];

    $this->user->givePermissionTo($this->permissions['finish']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/projects/finish', $project);
    $response->assertStatus(422);
});

test('finish project validation fails', function (): void {
    $project = [
        'id' => $this->projects[1]->id,
        'finish' => 'True',
    ];

    $this->user->givePermissionTo($this->permissions['finish']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/projects/finish', $project);
    $response->assertStatus(422);
});

test('get project', function (): void {
    $this->projects[0] = $this->projects[0]->refresh();

    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/projects/' . $this->projects[0]->id);
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $project = $json->data;
    expect($project)->not->toBeEmpty();
    expect($project->id)->toEqual($this->projects[0]->id);
    expect($project->client_id)->toEqual($this->projects[0]->client_id);
    expect($project->contact_id)->toEqual($this->projects[0]->contact_id);
    expect($project->order_id)->toEqual($this->projects[0]->order_id);
    expect($project->responsible_user_id)->toEqual($this->projects[0]->responsible_user_id);
    expect($project->parent_id)->toEqual($this->projects[0]->parent_id);
    expect($project->project_number)->toEqual($this->projects[0]->project_number);
    expect($project->name)->toEqual($this->projects[0]->name);
    expect($project->start_date)->toEqual(Carbon::parse($this->projects[0]->start_date)->toDateString());
    expect($project->end_date)->toBeNull();
    expect($project->description)->toEqual($this->projects[0]->description);
    expect($project->state)->toEqual($this->projects[0]->state);
    expect($project->progress)->toEqual($this->projects[0]->progress);
    expect($project->time_budget)->toEqual($this->projects[0]->time_budget);
    expect($project->budget)->toEqual($this->projects[0]->budget);
    expect(Carbon::parse($project->created_at))->toEqual(Carbon::parse($this->projects[0]->created_at));
    expect(Carbon::parse($project->updated_at))->toEqual(Carbon::parse($this->projects[0]->updated_at));
});

test('get project project not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/projects/' . ++$this->projects[1]->id);
    $response->assertStatus(404);
});

test('get projects', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/projects');
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    expect(property_exists($json, 'templates'))->toBeFalse();
    $projects = $json->data->data;
    $referenceProject = Project::query()->first();
    expect($projects)->not->toBeEmpty();
    expect($projects[0]->id)->toEqual($referenceProject->id);
    expect($projects[0]->client_id)->toEqual($referenceProject->client_id);
    expect($projects[0]->contact_id)->toEqual($referenceProject->contact_id);
    expect($projects[0]->order_id)->toEqual($referenceProject->order_id);
    expect($projects[0]->responsible_user_id)->toEqual($referenceProject->responsible_user_id);
    expect($projects[0]->parent_id)->toEqual($referenceProject->parent_id);
    expect($projects[0]->project_number)->toEqual($referenceProject->project_number);
    expect($projects[0]->name)->toEqual($referenceProject->name);
    expect($projects[0]->start_date)->toEqual(Carbon::parse($referenceProject->start_date)->toDateString());
    expect($projects[0]->end_date)->toEqual($referenceProject->end_date ?
        Carbon::parse($referenceProject->end_date)->toDateString() : null);
    expect($projects[0]->description)->toEqual($referenceProject->description);
    expect($projects[0]->state)->toEqual($referenceProject->state);
    expect($projects[0]->progress)->toEqual($referenceProject->progress);
    expect($projects[0]->time_budget)->toEqual($referenceProject->time_budget);
    expect($projects[0]->budget)->toEqual($referenceProject->budget);
    expect(Carbon::parse($projects[0]->created_at))->toEqual(Carbon::parse($referenceProject->created_at));
    expect(Carbon::parse($projects[0]->updated_at))->toEqual(Carbon::parse($referenceProject->updated_at));
});

test('reopen project', function (): void {
    $project = [
        'id' => $this->projects[1]->id,
        'finish' => false,
    ];

    $this->user->givePermissionTo($this->permissions['finish']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/projects/finish', $project);
    $response->assertStatus(200);

    $responseProject = json_decode($response->getContent())->data;
    $dbProject = Project::query()
        ->whereKey($responseProject->id)
        ->first();
    expect($dbProject)->not->toBeEmpty();
    expect($dbProject->id)->toEqual($project['id']);
    expect($dbProject->state)->toEqual(Project::getDefaultStateFor('state'));
});

test('update project', function (): void {
    $project = [
        'id' => $this->projects[0]->id,
        'contact_id' => null,
        'order_id' => null,
        'responsible_user_id' => null,
        'project_number' => Str::random(),
        'name' => Str::random(),
        'start_date' => date('Y-m-d'),
        'end_date' => null,
        'description' => 'New description text for further information',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/projects', $project);
    $response->assertStatus(200);

    $responseProject = json_decode($response->getContent())->data;
    $dbProject = (object) Project::query()
        ->whereKey($responseProject->id)
        ->first()
        ->append(['created_by', 'updated_by'])
        ->toArray();

    expect($dbProject)->not->toBeEmpty();
    expect($dbProject->id)->toEqual($project['id']);
    expect($dbProject->contact_id)->toEqual($project['contact_id']);
    expect($dbProject->order_id)->toEqual($project['order_id']);
    expect($dbProject->responsible_user_id)->toEqual($project['responsible_user_id']);
    expect($dbProject->parent_id)->toEqual($this->projects[0]->parent_id);
    expect($dbProject->project_number)->toEqual($project['project_number']);
    expect($dbProject->name)->toEqual($project['name']);
    expect(Carbon::parse($dbProject->start_date)->toDateString())->toEqual($project['start_date']);
    expect($dbProject->end_date)->toBeNull();
    expect($dbProject->description)->toEqual($project['description']);
});

test('update project contact not found', function (): void {
    $project = [
        'id' => $this->projects[1]->id,
        'contact_id' => ++$this->contact->id,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/projects', $project);
    $response->assertStatus(422);
    $response->assertJsonValidationErrorFor('contact_id');
});

test('update project order not found', function (): void {
    $project = [
        'id' => $this->projects[1]->id,
        'order_id' => ++$this->order->id,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/projects', $project);
    $response->assertStatus(422);
    $response->assertJsonValidationErrorFor('order_id');
});

test('update project project not found', function (): void {
    $project = [
        'id' => ++$this->projects[1]->id,
        'name' => 'Project Name',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/projects', $project);
    $response->assertStatus(422);
});

test('update project responsible user not found', function (): void {
    $project = [
        'id' => $this->projects[1]->id,
        'responsible_user_id' => ++$this->user->id,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/projects', $project);
    $response->assertStatus(422);
    $response->assertJsonValidationErrorFor('responsible_user_id');
});

test('update project validation fails', function (): void {
    $project = [
        'id' => $this->projects[0]->id,
        'state' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/projects', $project);
    $response->assertStatus(422);
});

test('update project with additional column', function (): void {
    $additionalColumns = AdditionalColumn::factory()->count(2)->create([
        'model_type' => morph_alias(Project::class),
    ]);

    $value = 'Original value from second additional column';
    $this->projects[0]->saveMeta($additionalColumns[0]->name, 'Original Value');
    $this->projects[0]->saveMeta($additionalColumns[1]->name, $value);

    $project = [
        'id' => $this->projects[0]->id,
        'contact_id' => null,
        'order_id' => null,
        'responsible_user_id' => null,
        'project_number' => Str::random(),
        'name' => Str::random(),
        'start_date' => date('Y-m-d'),
        'end_date' => null,
        'description' => 'New description text for further information',
        $additionalColumns[0]->name => 'New Value',
        $additionalColumns[1]->name => null,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/projects', $project);
    $response->assertStatus(200);

    $responseProject = json_decode($response->getContent())->data;
    $dbProject = Project::query()
        ->whereKey($responseProject->id)
        ->first();

    expect($dbProject)->not->toBeEmpty();
    expect($dbProject->id)->toEqual($project['id']);
    expect($dbProject->client_id)->toEqual($this->projects[0]->client_id);
    expect($dbProject->contact_id)->toEqual($project['contact_id']);
    expect($dbProject->order_id)->toEqual($project['order_id']);
    expect($dbProject->responsible_user_id)->toEqual($project['responsible_user_id']);
    expect($dbProject->parent_id)->toEqual($this->projects[0]->parent_id);
    expect($dbProject->project_number)->toEqual($project['project_number']);
    expect($dbProject->name)->toEqual($project['name']);
    expect(Carbon::parse($dbProject->start_date)->toDateString())->toEqual($project['start_date']);
    expect($dbProject->end_date)->toBeNull();
    expect($dbProject->description)->toEqual($project['description']);
    expect($this->user->is($dbProject->getUpdatedBy()))->toBeTrue();
    expect($responseProject->{$additionalColumns[0]->name})->toEqual($project[$additionalColumns[0]->name]);
    expect($dbProject->{$additionalColumns[0]->name})->toEqual($project[$additionalColumns[0]->name]);
    expect($responseProject->{$additionalColumns[1]->name})->toBeNull();
    expect($dbProject->{$additionalColumns[1]->name})->toBeNull();
});
