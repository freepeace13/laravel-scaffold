<?php

namespace Tests\Feature;

use Laravel\Sanctum\Sanctum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /**
     * @test
     */
    public function web_login_test()
    {
        $password = $this->faker->password;

        $user = factory(User::class)->create([
            'password' => bcrypt($password)
        ]);

        $this->assertDatabaseHas('users', [
            'email' => $user->email
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'invalid'
        ])
        ->assertRedirect()
        ->assertSessionHasErrorsIn('email');

        $this->assertGuest();

        $this->post('/login', [
            'email' => $user->email,
            'password' => $password
        ])
        ->assertSessionDoesntHaveErrors()
        ->assertRedirect();

        $this->assertAuthenticated();
    }

    /**
     * @test
     */
    public function api_login_test()
    {
        $password = $this->faker->password;

        $httpClient = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $user = factory(User::class)->create([
            'password' => bcrypt($password)
        ]);

        $this->assertDatabaseHas('users', [
            'email' => $user->email
        ]);

        $httpClient->json('GET', '/sanctum/csrf-cookie')
            ->assertNoContent();

        $httpClient->json('POST', '/api/login', [
            'email' => $user->email,
            'password' => 'invalid'
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

        $response = $httpClient->json('POST', '/api/login', [
            'email' => $user->email,
            'password' => $password
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['accessToken']);

        $accessTokenModel = Sanctum::personalAccessTokenModel();
        $accessTokenInstance = $accessTokenModel::findToken(
            json_decode($response->getContent())->accessToken
        );

        $this->assertTrue(! is_null($accessTokenInstance));
    }
}
