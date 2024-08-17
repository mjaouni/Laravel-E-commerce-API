<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration with valid data.
     *
     * @return void
     */

    public function test_user_registration_with_valid_data(): void
    {
        $response = $this->postJson('/api/v1/register', [
        'name'  => 'Test User',
        'email' => 'testuser777@gmail.com',
        'password' => 'password123',
        ]);

        $response->assertStatus(201)
              ->assertJson([
                'message' => 'User registered successfully',
              ])
              ->assertJsonStructure([
               'message',
               'user' => [
                   'id',
                   'name',
                   'email',
                   'created_at',
                   'updated_at',
               ],

           ]);

        $this->assertDatabaseHas('users', [
           'email' => 'testuser777@gmail.com'
        ]);
    }

    /**
     * Test for empty fields.
     *
     * @return void
    */

    public function test_user_registration_with_empty_fields(): void
    {
        // Empty name
        $response = $this->postJson('/api/v1/register', [
            'name' => '', //Empty Name
            'email' => '', //Empty Email
            'password' => '', //Empty Password
        ]);

        // 422 Unprocessable Content .The request was well-formed but was unable to be followed due to semantic errors.

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name','email','password']);
    }

    /**
     * Test for maximum length fields
     *
     * @return void
    */

    public function test_user_registration_with_max_length_exceeded(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => str_repeat('A', 256),
            'email' => str_repeat('a', 247) . '@example.com',
            'password' => str_repeat('P', 256),
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'email','password']);
    }

    /**
     * Test for minimum length fields
     *
     * @return void
    */

    public function test_user_registration_with_min_length_violations(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'A', // At least 3 char
            'email' => 'a@b.c', // No email min limit, this should pass
            'password' => 'short', // At least 8 char
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name','password']);
    }

    /**
     * Test for invalid format
     *
     * @return void
    */

    public function test_user_registration_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/v1/register', [
           'name' => 'Test User',
           'email' => 'not-an-email',
           'password' => 'password123',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test for existing email
     *
     * @return void
    */

    public function test_user_registration_with_existing_email(): void
    {
        User::factory()->create([
           'email' => 'testuser3@gmail.com'
        ]);

        $response = $this->postJson('/api/v1/register', [
           'name' => 'Test User',
           'email' => 'testuser3@gmail.com',
           'password' => 'password123',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test API rate limiting on the registration endpoint.
     *
     * @return void
    */

    public function test_registration_rate_limiting(): void
    {
       // Define the number of allowed requests and the URL to be tested
        $maxAttempts = 60; // limit to 60 request per second

        for ($i = 0; $i < $maxAttempts; $i++) {
            $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User' . ++$i,
            'email' => 'testuser3' . ++$i . '@gmail.com',
            'password' => 'password123' . ++$i,
            ]);

            $response->assertStatus(201); // Assuming a successful registration returns a 201 status code
        }

        // Simulate making one more request beyond the limit
        $response = $this->postJson($url, $registrationData);

        // Assert that the response indicates a rate limit has been exceeded
        $response->assertStatus(429); // 429 Too Many Requests
    }
}
