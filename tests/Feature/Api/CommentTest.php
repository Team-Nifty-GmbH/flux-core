<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Address;
use FluxErp\Models\Comment;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\Ticket;
use FluxErp\Models\Unit;
use FluxErp\Models\User;
use FluxErp\Notifications\Comment\CommentCreatedNotification;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

class CommentTest extends BaseSetup
{
    private Comment $comment;

    private array $permissions;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_create_comment(): void
    {
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
        $this->assertNotEmpty($dbComment);
        $this->assertEquals($comment['model_id'], $dbComment->model_id);
        $this->assertEquals(morph_alias(Ticket::class), $dbComment->model_type);
        $this->assertEquals($comment['comment'], $dbComment->comment);
        $this->assertTrue($this->user->is($dbComment->getCreatedBy()));
        $this->assertTrue($this->user->is($dbComment->getUpdatedBy()));
    }

    public function test_create_comment_model_class_not_found(): void
    {
        $comment = [
            'model_id' => 1,
            'model_type' => 'NotExistingTestModel',
            'comment' => 'test comment',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/comments', $comment);
        $response->assertStatus(422);
    }

    public function test_create_comment_model_instance_not_found(): void
    {
        $comment = [
            'model_id' => ++$this->ticket->id,
            'model_type' => morph_alias(Ticket::class),
            'comment' => 'test comment',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/comments', $comment);
        $response->assertStatus(422);
    }

    public function test_create_comment_not_commentable(): void
    {
        $comment = [
            'model_id' => 1,
            'model_type' => morph_alias(Unit::class),
            'comment' => 'test comment',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/comments', $comment);
        $response->assertStatus(422);
    }

    public function test_create_comment_sends_notification(): void
    {
        Notification::fake();
        $user = new User([
            'language_id' => $this->user->language_id,
            'email' => 'notification_user@example.com',
            'firstname' => 'firstname_notification_user',
            'lastname' => 'lastname',
            'password' => 'password',
        ]);
        $user->save();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);
        $address = Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->id,
            'is_main_address' => true,
        ]);

        $ticket = Ticket::factory()->create([
            'authenticatable_type' => morph_alias(Address::class),
            'authenticatable_id' => $address->id,
        ]);

        $comment = [
            'model_id' => $ticket->id,
            'model_type' => morph_alias(Ticket::class),
            'comment' => 'test comment <span class="mention" data-type="mention" data-id="user:'
                . $user->id . '">@firstname_notification_user lastname</span>',
            'is_internal' => false,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/comments', $comment);
        $response->assertStatus(201);

        $this->assertDatabaseHas('event_subscriptions', [
            'event' => 'eloquent.created: ' . Comment::class,
            'model_type' => morph_alias(Ticket::class),
            'model_id' => $ticket->id,
            'subscribable_type' => morph_alias(User::class),
            'subscribable_id' => $user->id,
        ]);

        Notification::assertSentTo($user, CommentCreatedNotification::class);
        Notification::assertNothingSentTo($this->user);
    }

    public function test_create_comment_validation_fails(): void
    {
        $comment = [
            'model_id' => $this->user->id,
            'comment' => 'test comment',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/comments', $comment);
        $response->assertStatus(422);
    }

    public function test_create_comment_with_parent(): void
    {
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

        $this->assertNotNull($dbComment);
        $this->assertEquals($comment['model_id'], $dbComment->model_id);
        $this->assertEquals($comment['model_type'], $dbComment->model_type);
        $this->assertEquals($comment['parent_id'], $dbComment->parent_id);
        $this->assertEquals($comment['comment'], $dbComment->comment);
    }

    public function test_create_comment_with_parent_not_found(): void
    {
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
    }

    public function test_delete_comment(): void
    {
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
        $this->assertNotNull($dbComment->deleted_at);
        $this->assertTrue($this->user->is($dbComment->getDeletedBy()));
    }

    public function test_delete_comment_as_super_admin(): void
    {
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
        $this->assertFalse($this->user->is($dbComment->getCreatedBy()));
        $this->assertNotNull($dbComment->deleted_at);
        $this->assertTrue($this->user->is($dbComment->getDeletedBy()));
    }

    public function test_delete_comment_comment_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/comments/' . $this->comment->id + 1);
        $response->assertStatus(404);
    }

    public function test_delete_comment_different_user(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/comments/' . $this->comment->id);
        $response->assertStatus(403);

        $dbComment = Comment::query()->whereKey($this->comment->id)->first();
        $this->assertNotNull($dbComment);
    }

    public function test_get_comments_model_instance_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show'])->load('permissions');
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/user/comments/' . ++$this->user->id);
        $response->assertStatus(404);
    }

    public function test_get_comments_route_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/notExistingTestModel/comments/' . $this->user->id);
        $response->assertStatus(404);
    }

    public function test_get_ticket_comments(): void
    {
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
        $this->assertFalse(property_exists($json, 'templates'));
        $ticketComment = $json->data->data;
        $this->assertNotEmpty($ticketComment);
        $this->assertEquals($dbComment->id, $ticketComment[0]->id);
        $this->assertEquals($dbComment->model_id, $ticketComment[0]->model_id);
        $this->assertEquals($dbComment->comment, $ticketComment[0]->comment);
    }

    public function test_update_comment(): void
    {
        $comment = [
            'id' => $this->comment->id,
            'is_sticky' => true,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/comments', $comment);
        $response->assertStatus(200);

        $dbComment = Comment::query()->whereKey($this->comment->id)->first();

        $this->assertEquals($this->comment->model_id, $dbComment->model_id);
        $this->assertEquals($this->comment->model_type, $dbComment->model_type);
        $this->assertEquals($this->comment->parent_id, $dbComment->parent_id);
        $this->assertEquals($this->comment->comment, $dbComment->comment);
        $this->assertEquals($comment['is_sticky'], $dbComment->is_sticky);
    }
}
