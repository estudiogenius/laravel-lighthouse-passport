<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    public function testAuth()
    {
        $user = factory(User::class)->create();

        $response = $this->graphql("mutation { auth(email: \"{$user->email}\", password: \"secret\") {token user{ id name } } }");

        $response->assertSee($user->name);

        $this->assertNotNull($response->json('data.auth.token'));
        $this->assertNotNull($response->json('data.auth.user.id'));
        $this->assertNotNull($response->json('data.auth.user.name'));
    }

    public function testAuthUsingVariable()
    {
        $user = factory(User::class)->create();

        $response = $this->graphql('
            mutation Login($email: String!, $password: String!){ 
                auth(email: $email, password: $password) {
                    token
                    user{ id name } 
                } 
            }', null, ['email' => $user->email, 'password' => 'secret']);

        $response->assertSee($user->name);

        $this->assertNotNull($response->json('data.auth.token'));
        $this->assertNotNull($response->json('data.auth.user.id'));
        $this->assertNotNull($response->json('data.auth.user.name'));
    }

    public function testCheckAuth()
    {
        $user = factory(User::class)->create();

        $response = $this->graphql("{ auth { id name } }", $user);

        $response->assertSee($user->name);

        $this->assertNotNull($response->json('data.auth.id'));
        $this->assertNotNull($response->json('data.auth.name'));
    }

    public function testCheckAuthWithNoToken()
    {
        $user = factory(User::class)->create();

        $response = $this->graphql("{ auth { id name } }");

        $response->assertDontSee($user->name);

        $this->assertNull($response->json('data.auth'));
    }
}
