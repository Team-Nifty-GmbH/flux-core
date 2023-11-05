<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Comment;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\Unit;
use FluxErp\Models\User;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class CommentTest extends BaseSetup
{
    use DatabaseTransactions;

    private Comment $comment;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->comment = Comment::factory()->create([
            'model_type' => User::class,
            'model_id' => $this->user->id,
            'comment' => 'User Comment from a Test!',
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.{modeltype}.comments.{id}.get'),
            'create' => Permission::findOrCreate('api.comments.post'),
            'update' => Permission::findOrCreate('api.comments.update'),
            'delete' => Permission::findOrCreate('api.comments.{id}.delete'),
        ];
        Role::findOrCreate('Super Admin');
    }

    public function test_get_user_comments()
    {
        $dbComment = new Comment();
        $dbComment->model_type = User::class;
        $dbComment->model_id = $this->user->id;
        $dbComment->comment = 'User Comment from a Test!';
        $dbComment->save();

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/user/comments/' . $this->user->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $this->assertFalse(property_exists($json, 'templates'));
        $userComment = $json->data->data;
        $this->assertNotEmpty($userComment);
        $this->assertEquals($dbComment->id, $userComment[0]->id);
        $this->assertEquals($dbComment->model_id, $userComment[0]->model_id);
        $this->assertEquals($dbComment->comment, $userComment[0]->comment);
    }

    public function test_get_comments_route_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/notExistingTestModel/comments/' . $this->user->id);
        $response->assertStatus(404);
    }

    public function test_get_comments_model_instance_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/user/comments/' . ++$this->user->id);
        $response->assertStatus(404);
    }

    public function test_create_comment()
    {
        $comment = [
            'model_id' => $this->user->id,
            'model_type' => User::class,
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
        $this->assertEquals(User::class, $dbComment->model_type);
        $this->assertEquals($comment['comment'], $dbComment->comment);
        $this->assertEquals($this->user->id, $dbComment->created_by->id);
        $this->assertEquals($this->user->id, $dbComment->updated_by->id);
    }

    public function test_create_comment_validation_fails()
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

    public function test_create_comment_model_class_not_found()
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

    public function test_create_comment_not_commentable()
    {
        $comment = [
            'model_id' => 1,
            'model_type' => class_basename(Unit::class),
            'comment' => 'test comment',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/comments', $comment);
        $response->assertStatus(422);
    }

    public function test_create_comment_model_instance_not_found()
    {
        $comment = [
            'model_id' => ++$this->user->id,
            'model_type' => class_basename(User::class),
            'comment' => 'test comment',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/comments', $comment);
        $response->assertStatus(422);
    }

    public function test_create_comment_with_parent()
    {
        $comment = [
            'model_id' => $this->user->id,
            'model_type' => User::class,
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
        $this->assertEquals(User::class, $dbComment->model_type);
        $this->assertEquals($comment['parent_id'], $dbComment->parent_id);
        $this->assertEquals($comment['comment'], $dbComment->comment);
    }

    public function test_create_comment_with_parent_not_found()
    {
        $comment = [
            'model_id' => $this->user->id,
            'model_type' => class_basename(User::class),
            'parent_id' => ++$this->comment->id,
            'comment' => 'child comment',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/comments', $comment);
        $response->assertStatus(422);
    }

    public function test_update_comment()
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

    public function test_delete_comment()
    {
        $activity = $this->comment->activities()->where('event', 'created')->first();
        $activity->causer()->associate($this->user);
        $activity->save();

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/comments/' . $this->comment->id);
        $response->assertStatus(204);

        $dbComment = $this->comment->fresh();
        $this->assertNotNull($dbComment->deleted_at);
        $this->assertEquals($this->user->id, $dbComment->deleted_by->id);
    }

    public function test_delete_comment_different_user()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/comments/' . $this->comment->id);
        $response->assertStatus(403);

        $dbComment = Comment::query()->whereKey($this->comment->id)->first();
        $this->assertNotNull($dbComment);
    }

    public function test_delete_comment_comment_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/comments/' . $this->comment->id + 1);
        $response->assertStatus(404);
    }

    public function test_delete_comment_as_super_admin()
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
        $this->assertNotEquals($dbComment->created_by->id, $this->user->id);
        $this->assertNotNull($dbComment->deleted_at);
        $this->assertEquals($this->user->id, $dbComment->deleted_by->id);
    }
}
