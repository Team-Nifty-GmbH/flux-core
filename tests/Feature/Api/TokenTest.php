<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Client;
use FluxErp\Models\Permission;
use FluxErp\Models\Token;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;

class TokenTest extends BaseSetup
{
    private array $permissions;

    private Collection $tokens;

    protected function setUp(): void
    {
        parent::setUp();

        $dbClient = Client::factory()->create();

        $this->tokens = collect();
        for ($i = 0; $i < 3; $i++) {
            $token = new Token([
                'name' => 'Test Token ' . ($i + 1),
                'abilities' => ['*'],
                'max_uses' => $i === 0 ? 0 : 10,
                'expires_at' => $i === 2 ? now()->addDays(30) : null,
            ]);
            $token->save();
            $this->tokens->push($token);
        }

        $this->user->clients()->attach($dbClient->id);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.tokens.{id}.get'),
            'index' => Permission::findOrCreate('api.tokens.get'),
            'create' => Permission::findOrCreate('api.tokens.post'),
            'delete' => Permission::findOrCreate('api.tokens.{id}.delete'),
        ];
    }

    public function test_create_token(): void
    {
        $token = [
            'name' => 'Test API Token',
            'abilities' => ['*'],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/tokens', $token);
        $response->assertStatus(201);

        $responseToken = json_decode($response->getContent())->data;
        $dbToken = Token::query()
            ->whereKey($responseToken->id)
            ->first();

        $this->assertNotEmpty($dbToken);
        $this->assertEquals($token['name'], $dbToken->name);
        $this->assertEquals($token['abilities'], $dbToken->abilities);
        $this->assertEquals(0, $dbToken->max_uses);
        $this->assertNull($dbToken->expires_at);
        $this->assertNotNull($responseToken->plain_text_token);
    }

    public function test_create_token_maximum(): void
    {
        Permission::findOrCreate('test.permission.one');
        Permission::findOrCreate('test.permission.two');

        $token = [
            'name' => 'Full Test Token',
            'abilities' => ['*'],
            'max_uses' => 100,
            'expires_at' => now()->addDays(30),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/tokens', $token);
        $response->assertStatus(201);

        $responseToken = json_decode($response->getContent())->data;
        $dbToken = Token::query()
            ->whereKey($responseToken->id)
            ->first();

        $this->assertNotEmpty($dbToken);
        $this->assertEquals($token['name'], $dbToken->name);
        $this->assertEquals($token['abilities'], $dbToken->abilities);
        $this->assertEquals($token['max_uses'], $dbToken->max_uses);
        $this->assertEquals($token['expires_at'], $dbToken->expires_at->toDateTimeString());
        $this->assertNotNull($responseToken->plain_text_token);
    }

    public function test_create_token_validation_fails(): void
    {
        $token = [
            'name' => '',
            'max_uses' => -1,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/tokens', $token);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'name',
            'max_uses',
        ]);
    }

    public function test_delete_token(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->delete('/api/tokens/' . $this->tokens[0]->id);
        $response->assertStatus(204);

        $token = $this->tokens[0]->fresh();
        $this->assertNull($token);
    }

    public function test_delete_token_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->delete('/api/tokens/' . (Token::max('id') + 1));
        $response->assertStatus(404);
    }

    public function test_get_token(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/tokens/' . $this->tokens[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonToken = $json->data;

        $this->assertNotEmpty($jsonToken);
        $this->assertEquals($this->tokens[0]->id, $jsonToken->id);
        $this->assertEquals($this->tokens[0]->name, $jsonToken->name);
        $this->assertEquals($this->tokens[0]->abilities, $jsonToken->abilities);
        $this->assertEquals($this->tokens[0]->max_uses, $jsonToken->max_uses);
    }

    public function test_get_token_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/tokens/' . (Token::max('id') + 1));
        $response->assertStatus(404);
    }

    public function test_get_tokens(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/tokens');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonTokens = collect($json->data->data);

        $this->assertGreaterThanOrEqual(3, count($jsonTokens));

        foreach ($this->tokens as $token) {
            $jsonTokens->contains(function ($jsonToken) use ($token) {
                return $jsonToken->id === $token->id &&
                    $jsonToken->name === $token->name &&
                    $jsonToken->abilities === $token->abilities &&
                    $jsonToken->max_uses === $token->max_uses;
            });
        }
    }

    public function test_token_validation_methods(): void
    {
        $expiredToken = new Token([
            'name' => 'Expired Token',
            'expires_at' => now()->subDay(),
        ]);
        $expiredToken->save();

        $this->assertTrue($expiredToken->hasExpired());
        $this->assertFalse($expiredToken->isValid());

        $maxUsesToken = new Token([
            'name' => 'Max Uses Token',
            'max_uses' => 1,
            'uses' => 1,
        ]);
        $maxUsesToken->save();

        $this->assertTrue($maxUsesToken->hasExceededMaxUsage());
        $this->assertFalse($maxUsesToken->isValid());

        $validToken = new Token([
            'name' => 'Valid Token',
            'max_uses' => 0,
            'expires_at' => null,
            'url' => request()->url(),
        ]);
        $validToken->save();

        $this->assertFalse($validToken->hasExpired());
        $this->assertFalse($validToken->hasExceededMaxUsage());
        $this->assertTrue($validToken->isValid());
    }
}
