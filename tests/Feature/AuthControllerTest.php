<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    use RefreshDatabase;

    /**
     * Test user registration with valid data.
     *
     * @return void
     */

     public function test_user_registration_with_valid_data()
     {
      $response = $this->postJson('/api/register',[
        'name'  => 'Test User',
        'email' => 'testuser@gmail.com',
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

        $this->assertDatabaseHas('users',[
            'email'=>'testuser@gmail.com'
        ]);    

     }

    // Test for empty fields
    public function test_user_registration_with_empty_fields()
    {
        // Empty name
        $response = $this->postJson('/api/register', [
            'name' => '', //Empty Name
            'email' => '', //Empty Email
            'password' => '', //Empty Password
        ]);

        // 422 Unprocessable Content .The request was well-formed but was unable to be followed due to semantic errors.

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name','email','password']);

    }

    // Test for maximum length fields
    public function test_user_registration_with_max_length_exceeded()
    {
        $response = $this->postJson('/api/register', [
            'name' => str_repeat('A', 256),
            'email' => str_repeat('a', 247) . '@example.com',
            'password' => str_repeat('P', 256),
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'email','password']);
    }

     // Test for minimum length fields
     public function test_user_registration_with_min_length_violations()
     {
         $response = $this->postJson('/api/register', [
             'name' => 'A', // At least 3 char
             'email' => 'a@b.c', // No email min limit, this should pass
             'password' => 'short', // At least 8 char
         ]);
         $response->assertStatus(422)
                  ->assertJsonValidationErrors(['name','password']);
     }

     // Test for invalid format
     public function test_user_registration_with_invalid_email_format()
     {
        $response = $this->postJson('/api/register',[
            'name' => 'Test User',
            'email'=> 'not-an-email',
            'password'=> 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
     }

     // Test for existing format
     public function test_user_registration_with_existing_email()
     {
        User::factory()->create([
            'email' => 'testuser5@gmail.com'
        ]);

        $response = $this->postJson('/api/register',[
            'name' => 'Test User',
            'email'=> 'testuser5@gmail.com',
            'password'=> 'password123',
        ]);

        $response->assertStatus(400)
                 ->assertJsonValidationErrors(['email']);
     }   
   
}
