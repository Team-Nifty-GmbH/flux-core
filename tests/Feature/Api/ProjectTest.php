<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\States\Project\Done;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class ProjectTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $projects;

    private Contact $contact;

    private Order $order;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $addresses = Address::factory()->count(2)->create([
            'client_id' => $this->dbClient->id,
            'contact_id' => $this->contact->id,
        ]);

        $priceList = PriceList::factory()->create();

        $currency = Currency::factory()->create([
            'is_default' => true,
        ]);

        $language = Language::factory()->create();

        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->id,
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $paymentType = PaymentType::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $this->order = Order::factory()->create([
            'client_id' => $this->dbClient->id,
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
    }

    public function test_get_project()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/projects/' . $this->projects[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $project = $json->data;
        $this->assertNotEmpty($project);
        $this->assertEquals($this->projects[0]->id, $project->id);
        $this->assertEquals($this->projects[0]->contact_id, $project->contact_id);
        $this->assertEquals($this->projects[0]->order_id, $project->order_id);
        $this->assertEquals($this->projects[0]->responsible_user_id, $project->responsible_user_id);
        $this->assertEquals($this->projects[0]->parent_id, $project->parent_id);
        $this->assertEquals($this->projects[0]->project_number, $project->project_number);
        $this->assertEquals($this->projects[0]->name, $project->name);
        $this->assertEquals(Carbon::parse($this->projects[0]->start_date)->toDateString(), $project->start_date);
        $this->assertNull($project->end_date);
        $this->assertEquals($this->projects[0]->description, $project->description);
        $this->assertEquals($this->projects[0]->state, $project->state);
        $this->assertEquals($this->projects[0]->progress, $project->progress);
        $this->assertEquals($this->projects[0]->time_budget, $project->time_budget);
        $this->assertEquals($this->projects[0]->budget, $project->budget);
        $this->assertEquals(Carbon::parse($this->projects[0]->created_at),
            Carbon::parse($project->created_at));
        $this->assertEquals(Carbon::parse($this->projects[0]->updated_at),
            Carbon::parse($project->updated_at));
    }

    public function test_get_project_project_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/projects/' . ++$this->projects[1]->id);
        $response->assertStatus(404);
    }

    public function test_get_projects()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/projects');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $this->assertFalse(property_exists($json, 'templates'));
        $projects = $json->data->data;
        $referenceProject = Project::query()->first();
        $this->assertNotEmpty($projects);
        $this->assertEquals($referenceProject->id, $projects[0]->id);
        $this->assertEquals($referenceProject->contact_id, $projects[0]->contact_id);
        $this->assertEquals($referenceProject->order_id, $projects[0]->order_id);
        $this->assertEquals($referenceProject->responsible_user_id, $projects[0]->responsible_user_id);
        $this->assertEquals($referenceProject->parent_id, $projects[0]->parent_id);
        $this->assertEquals($referenceProject->project_number, $projects[0]->project_number);
        $this->assertEquals($referenceProject->name, $projects[0]->name);
        $this->assertEquals(Carbon::parse($referenceProject->start_date)->toDateString(), $projects[0]->start_date);
        $this->assertEquals($referenceProject->deadline ?
            Carbon::parse($referenceProject->deadline)->toDateString() : null, $projects[0]->deadline);
        $this->assertEquals($referenceProject->description, $projects[0]->description);
        $this->assertEquals($referenceProject->state, $projects[0]->state);
        $this->assertEquals($referenceProject->progress, $projects[0]->progress);
        $this->assertEquals($referenceProject->time_budget, $projects[0]->time_budget);
        $this->assertEquals($referenceProject->budget, $projects[0]->budget);
        $this->assertEquals(Carbon::parse($referenceProject->created_at), Carbon::parse($projects[0]->created_at));
        $this->assertEquals(Carbon::parse($referenceProject->updated_at), Carbon::parse($projects[0]->updated_at));
    }

    public function test_create_project()
    {
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
            'time_budget' => '06:40',
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
        $this->assertNotEmpty($dbProject);
        $this->assertEquals($project['parent_id'], $dbProject->parent_id);
        $this->assertEquals($project['contact_id'], $dbProject->contact_id);
        $this->assertEquals($project['order_id'], $dbProject->order_id);
        $this->assertEquals($project['responsible_user_id'], $dbProject->responsible_user_id);
        $this->assertEquals($project['project_number'], $dbProject->project_number);
        $this->assertEquals($project['name'], $dbProject->name);
        $this->assertEquals($project['start_date'], Carbon::parse($dbProject->start_date)->toDateString());
        $this->assertEquals($project['end_date'], Carbon::parse($dbProject->end_date)->toDateString());
        $this->assertEquals($project['description'], $dbProject->description);
        $this->assertEquals($project['time_budget'], $dbProject->time_budget);
        $this->assertEquals($project['budget'], $dbProject->budget);
        $this->assertEquals($this->user->id, $dbProject->created_by->id);
        $this->assertEquals($this->user->id, $dbProject->updated_by->id);
    }

    public function test_create_project_validation_fails()
    {
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
    }

    public function test_create_project_contact_not_found()
    {
        $project = [
            'contact_id' => ++$this->contact->id,
            'name' => 'Project Name',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_create_project_order_not_found()
    {
        $project = [
            'parent_id' => ++$this->order->id,
            'name' => 'Project Name',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_create_project_responsible_user_not_found()
    {
        $project = [
            'parent_id' => ++$this->user->id,
            'name' => 'Project Name',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_create_project_parent_project_not_found()
    {
        $project = [
            'parent_id' => ++$this->projects[1]->id,
            'name' => 'Project Name',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_update_project()
    {
        $project = [
            'id' => $this->projects[0]->id,
            'contact_id' => null,
            'order_id' => null,
            'responsible_user_id' => null,
            'parent_id' => $this->projects[1]->id,
            'project_number' => Str::random(),
            'name' => Str::random(),
            'start_date' => date('Y-m-d'),
            'description' => 'New description text for further information',
            'end_date' => null,
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

        $this->assertNotEmpty($dbProject);
        $this->assertEquals($project['id'], $dbProject->id);
        $this->assertEquals($project['contact_id'], $dbProject->contact_id);
        $this->assertEquals($project['order_id'], $dbProject->order_id);
        $this->assertEquals($project['responsible_user_id'], $dbProject->responsible_user_id);
        $this->assertEquals($project['parent_id'], $dbProject->parent_id);
        $this->assertEquals($project['project_name'], $dbProject->project_name);
        $this->assertEquals($project['start_date'], Carbon::parse($dbProject->start_date)->toDateString());
        $this->assertNull($dbProject->end_date);
        $this->assertEquals($project['description'], $dbProject->description);
    }

    public function test_update_project_with_additional_column()
    {
        $additionalColumns = AdditionalColumn::factory()->count(2)->create([
            'model_type' => Project::class,
        ]);

        $value = 'Original value from second additional column';
        $this->projects[0]->saveMeta($additionalColumns[0]->name, 'Original Value');
        $this->projects[0]->saveMeta($additionalColumns[1]->name, $value);

        $project = [
            'id' => $this->projects[0]->id,
            'contact_id' => null,
            'order_id' => null,
            'responsible_user_id' => null,
            'parent_id' => $this->projects[1]->id,
            'project_number' => Str::random(),
            'name' => Str::random(),
            'start_date' => date('Y-m-d'),
            'description' => 'New description text for further information',
            'end_date' => null,
            $additionalColumns[0]->name => 'New Value',
            $additionalColumns[1]->name => null,
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

        $this->assertNotEmpty($dbProject);
        $this->assertEquals($project['id'], $dbProject->id);
        $this->assertEquals($project['contact_id'], $dbProject->contact_id);
        $this->assertEquals($project['order_id'], $dbProject->order_id);
        $this->assertEquals($project['responsible_user_id'], $dbProject->responsible_user_id);
        $this->assertEquals($project['parent_id'], $dbProject->parent_id);
        $this->assertEquals($project['project_name'], $dbProject->project_name);
        $this->assertEquals($project['start_date'], Carbon::parse($dbProject->start_date)->toDateString());
        $this->assertNull($dbProject->end_date);
        $this->assertEquals($project['description'], $dbProject->description);
        $this->assertEquals($this->user->id, $dbProject->updated_by['id']);
        $this->assertEquals($project[$additionalColumns[0]->name], $responseProject->{$additionalColumns[0]->name});
        $this->assertEquals($project[$additionalColumns[0]->name], $dbProject->{$additionalColumns[0]->name});
        $this->assertNull($responseProject->{$additionalColumns[1]->name});
        $this->assertNull($dbProject->{$additionalColumns[1]->name});
    }

    public function test_update_project_validation_fails()
    {
        $project = [
            'id' => $this->projects[0]->id,
            'state' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_update_project_project_not_found()
    {
        $project = [
            'id' => ++$this->projects[1]->id,
            'name' => 'Project Name',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_update_project_contact_not_found()
    {
        $project = [
            'id' => $this->projects[1]->id,
            'contact_id' => ++$this->contact->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_update_project_order_not_found()
    {
        $project = [
            'id' => $this->projects[1]->id,
            'order_id' => ++$this->order->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_update_project_responsible_user_not_found()
    {
        $project = [
            'id' => $this->projects[1]->id,
            'responsible_user_id' => ++$this->user->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_update_project_parent_project_not_found()
    {
        $project = [
            'id' => $this->projects[1]->id,
            'parent_id' => ++$this->projects[1]->id,
            'name' => 'Project Name',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/projects', $project);
        $response->assertStatus(422);
    }

    public function test_delete_project()
    {
        AdditionalColumn::factory()->create([
            'model_type' => Project::class,
        ]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/projects/' . $this->projects[1]->id);
        $response->assertStatus(204);

        $project = $this->projects[1]->fresh();
        $this->assertNotNull($project->deleted_at);
        $this->assertEquals($this->user->id, $project->deleted_by->id);
    }

    public function test_delete_project_project_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/projects/' . ++$this->projects[1]->id);
        $response->assertStatus(404);
    }

    public function test_delete_project_project_has_children()
    {
        $this->projects[0]->parent_id = $this->projects[1]->id;
        $this->projects[0]->save();

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/projects/' . $this->projects[1]->id);
        $response->assertStatus(423);
    }

    public function test_finish_project()
    {
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
        $this->assertNotEmpty($dbProject);
        $this->assertEquals($project['id'], $dbProject->id);
        $this->assertEquals(Done::class, get_class($dbProject->state));
    }

    public function test_reopen_project()
    {
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
        $this->assertNotEmpty($dbProject);
        $this->assertEquals($project['id'], $dbProject->id);
        $this->assertEquals(Project::getDefaultStateFor('state'), $dbProject->state);
    }

    public function test_finish_project_validation_fails()
    {
        $project = [
            'id' => $this->projects[1]->id,
            'finish' => 'True',
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/finish', $project);
        $response->assertStatus(422);
    }

    public function test_finish_project_project_not_found()
    {
        $project = [
            'id' => ++$this->projects[1]->id,
            'finish' => true,
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/projects/finish', $project);
        $response->assertStatus(422);
    }
}
