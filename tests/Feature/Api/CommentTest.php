<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use FluxErp\Models\Address;
use FluxErp\Models\Comment;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\Ticket;
use FluxErp\Models\Unit;
use FluxErp\Models\User;
use FluxErp\Notifications\Comment\CommentCreatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $this->user->id,
    ]);
    $this->comment = Comment::factory()->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->id,
        'comment' => 'User Comment from a Test!',
    ]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.{modeltype}.comments.{id}.get'),
        'create' => Permission::findOrCreate('api.comments.post'),
        'update' => Permission::findOrCreate('api.comments.put'),
        'delete' => Permission::findOrCreate('api.comments.{id}.delete'),
    ];
    Role::findOrCreate('Super Admin');
});

test('create comment', function (): void {
    $comment = [
        'model_id' => $this->ticket->id,
        'model_type' => morph_alias(Ticket::class),
        'comment' => 'test comment',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/comments', $comment);
    $response->assertStatus(201);

    $userComment = json_decode($response->getContent())->data;
    $dbComment = Comment::query()
        ->whereKey($userComment->id)
        ->first();
    expect($dbComment)->not->toBeEmpty();
    expect($dbComment->model_id)->toEqual($comment['model_id']);
    expect($dbComment->model_type)->toEqual(morph_alias(Ticket::class));
    expect($dbComment->comment)->toEqual($comment['comment']);
    expect($this->user->is($dbComment->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbComment->getUpdatedBy()))->toBeTrue();
});

test('create comment model class not found', function (): void {
    $comment = [
        'model_id' => 1,
        'model_type' => 'NotExistingTestModel',
        'comment' => 'test comment',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/comments', $comment);
    $response->assertStatus(422);
});

test('create comment model instance not found', function (): void {
    $comment = [
        'model_id' => ++$this->ticket->id,
        'model_type' => morph_alias(Ticket::class),
        'comment' => 'test comment',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/comments', $comment);
    $response->assertStatus(422);
});

test('create comment not commentable', function (): void {
    $comment = [
        'model_id' => 1,
        'model_type' => morph_alias(Unit::class),
        'comment' => 'test comment',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/comments', $comment);
    $response->assertStatus(422);
});

test('create comment sends notification', function (): void {
    Notification::fake();
    config(['queue.default' => 'sync']);

    $user = new User([
        'language_id' => $this->user->language_id,
        'email' => 'notification_user@example.com',
        'firstname' => 'firstname_notification_user',
        'lastname' => 'lastname',
        'password' => 'password',
    ]);
    $user->save();

    $address = Address::factory()
        ->for(Contact::factory()->create(['client_id' => $this->dbClient->getKey()]))
        ->create([
            'client_id' => $this->dbClient->getKey(),
            'is_main_address' => true,
        ]);

    $ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $address->id,
    ]);
    $address->subscribeNotificationChannel($ticket->broadcastChannel());

    $comment = [
        'model_id' => $ticket->id,
        'model_type' => morph_alias(Ticket::class),
        'comment' => 'test comment <span class="mention" data-type="mention" data-id="user:'
            . $user->id . '">@firstname_notification_user lastname</span>',
        'is_active' => true,
        'is_internal' => true,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/comments', $comment);
    $response->assertStatus(201);

    $this->assertDatabaseHas('event_subscriptions', [
        'channel' => $ticket->broadcastChannel(),
        'subscribable_type' => morph_alias(User::class),
        'subscribable_id' => $user->id,
    ]);

    Notification::assertSentTo($user, CommentCreatedNotification::class);
    Notification::assertNothingSentTo($address);
    Notification::assertNothingSentTo($this->user);
});

test('create comment sends notification to address', function (): void {
    Notification::fake();
    config(['queue.default' => 'sync']);

    $address = Address::factory()
        ->for(Contact::factory()->create(['client_id' => $this->dbClient->id]))
        ->create([
            'client_id' => $this->dbClient->id,
            'is_active' => true,
            'is_main_address' => true,
        ]);

    $ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $address->id,
    ]);
    $address->subscribeNotificationChannel($ticket->broadcastChannel());

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this
        ->actingAs($this->user)
        ->post('/api/comments', [
            'model_id' => $ticket->id,
            'model_type' => morph_alias(Ticket::class),
            'comment' => 'test comment',
            'is_internal' => false,
        ]);
    $response->assertStatus(201);

    Notification::assertSentTo($address, CommentCreatedNotification::class);
});

test('create comment validation fails', function (): void {
    $comment = [
        'model_id' => $this->user->id,
        'comment' => 'test comment',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/comments', $comment);
    $response->assertStatus(422);
});

test('create comment with parent', function (): void {
    $comment = [
        'model_id' => $this->ticket->id,
        'model_type' => morph_alias(Ticket::class),
        'parent_id' => $this->comment->id,
        'comment' => 'child comment',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/comments', $comment);
    $response->assertStatus(201);

    $userComment = json_decode($response->getContent())->data;
    $dbComment = Comment::query()->whereKey($userComment->id)->first();

    expect($dbComment)->not->toBeNull();
    expect($dbComment->model_id)->toEqual($comment['model_id']);
    expect($dbComment->model_type)->toEqual($comment['model_type']);
    expect($dbComment->parent_id)->toEqual($comment['parent_id']);
    expect($dbComment->comment)->toEqual($comment['comment']);
});

test('create comment with parent not found', function (): void {
    $comment = [
        'model_id' => $this->ticket->id,
        'model_type' => morph_alias(Ticket::class),
        'parent_id' => ++$this->comment->id,
        'comment' => 'child comment',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/comments', $comment);
    $response->assertStatus(422);
});

test('delete comment', function (): void {
    DB::table($this->comment->getTable())
        ->where($this->comment->getKeyName(), $this->comment->getKey())
        ->update([
            'created_by' => $this->user->getMorphClass() . ':' . $this->user->getKey(),
        ]);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/comments/' . $this->comment->id);
    $response->assertStatus(204);

    $dbComment = $this->comment->fresh();
    expect($dbComment->deleted_at)->not->toBeNull();
    expect($this->user->is($dbComment->getDeletedBy()))->toBeTrue();
});

test('delete comment as super admin', function (): void {
    $user = User::factory()->create([
        'language_id' => $this->user->language_id,
    ]);

    $activity = $this->comment->activities()->where('event', 'created')->first();
    $activity->causer()->associate($user);
    $activity->save();

    $this->user->assignRole('Super Admin');
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/comments/' . $this->comment->id);
    $response->assertStatus(204);

    $dbComment = $this->comment->fresh();
    expect($this->user->is($dbComment->getCreatedBy()))->toBeFalse();
    expect($dbComment->deleted_at)->not->toBeNull();
    expect($this->user->is($dbComment->getDeletedBy()))->toBeTrue();
});

test('delete comment comment not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/comments/' . $this->comment->id + 1);
    $response->assertStatus(404);
});

test('delete comment different user', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/comments/' . $this->comment->id);
    $response->assertStatus(403);

    $dbComment = Comment::query()->whereKey($this->comment->id)->first();
    expect($dbComment)->not->toBeNull();
});

test('get comments model instance not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show'])->load('permissions');
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/user/comments/' . ++$this->user->id);
    $response->assertStatus(404);
});

test('get comments route not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/notExistingTestModel/comments/' . $this->user->id);
    $response->assertStatus(404);
});

test('get ticket comments', function (): void {
    $dbComment = new Comment();
    $dbComment->model_type = morph_alias(Ticket::class);
    $dbComment->model_id = $this->ticket->id;
    $dbComment->comment = 'User Comment from a Test!';
    $dbComment->save();

    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/ticket/comments/' . $this->ticket->id);
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    expect(property_exists($json, 'templates'))->toBeFalse();
    $ticketComment = $json->data->data;
    expect($ticketComment)->not->toBeEmpty();
    expect($ticketComment[0]->id)->toEqual($dbComment->id);
    expect($ticketComment[0]->model_id)->toEqual($dbComment->model_id);
    expect($ticketComment[0]->comment)->toEqual($dbComment->comment);
});

test('update comment', function (): void {
    $comment = [
        'id' => $this->comment->id,
        'is_sticky' => true,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/comments', $comment);
    $response->assertStatus(200);

    $dbComment = Comment::query()->whereKey($this->comment->id)->first();

    expect($dbComment->model_id)->toEqual($this->comment->model_id);
    expect($dbComment->model_type)->toEqual($this->comment->model_type);
    expect($dbComment->parent_id)->toEqual($this->comment->parent_id);
    expect($dbComment->comment)->toEqual($this->comment->comment);
    expect($dbComment->is_sticky)->toEqual($comment['is_sticky']);
});
