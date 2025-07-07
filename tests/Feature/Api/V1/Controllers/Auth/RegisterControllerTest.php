<?php

namespace Tests\Feature\Api\V1\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('RegisterController', function () {
    describe('Register', function () {
        test('registers user with valid data', function () {
            $userData = [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ];

            $response = $this->postJson('/api/v1/register', $userData);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                ])
                ->assertJson([
                    'user' => [
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                    ],
                ]);

            expect($response->json('token'))->toBeString()->not->toBeEmpty();            // Verify user was created in database
            $this->assertDatabaseHas('users', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ]);

            // Verify password was hashed and can be verified
            $user = User::where('email', 'john@example.com')->first();
            expect(Hash::check('Password123!', $user->getAuthPassword()))->toBeTrue();
        });

        test('returns validation error for missing name', function () {
            $response = $this->postJson('/api/v1/register', [
                'email' => 'john@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        test('returns validation error for missing email', function () {
            $response = $this->postJson('/api/v1/register', [
                'name' => 'John Doe',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('returns validation error for missing password', function () {
            $response = $this->postJson('/api/v1/register', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password_confirmation' => 'Password123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        test('returns validation error for invalid email format', function () {
            $response = $this->postJson('/api/v1/register', [
                'name' => 'John Doe',
                'email' => 'invalid-email',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('returns validation error for duplicate email', function () {
            User::factory()->create(['email' => 'john@example.com']);

            $response = $this->postJson('/api/v1/register', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('returns validation error for name exceeding max length', function () {
            $response = $this->postJson('/api/v1/register', [
                'name' => str_repeat('a', 256), // Exceeds 255 character limit
                'email' => 'john@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        test('returns validation error for password shorter than 8 characters', function () {
            $response = $this->postJson('/api/v1/register', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'Pass1!',
                'password_confirmation' => 'Pass1!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        test('returns validation error for password without uppercase letter', function () {
            $response = $this->postJson('/api/v1/register', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'password123!',
                'password_confirmation' => 'password123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        test('returns validation error for password without lowercase letter', function () {
            $response = $this->postJson('/api/v1/register', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'PASSWORD123!',
                'password_confirmation' => 'PASSWORD123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        test('returns validation error for password without special character', function () {
            $response = $this->postJson('/api/v1/register', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        test('returns validation error for password confirmation mismatch', function () {
            $response = $this->postJson('/api/v1/register', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'DifferentPassword123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        test('applies guest middleware - authenticated users cannot access', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user, 'sanctum')
                ->postJson('/api/v1/register', [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'password' => 'Password123!',
                    'password_confirmation' => 'Password123!',
                ]);

            $response->assertStatus(302); // Redirect response from guest middleware
        });

        test('token is valid for API access', function () {
            $registerResponse = $this->postJson('/api/v1/register', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

            $token = $registerResponse->json('token');
            $userId = $registerResponse->json('user.id');

            // Use the token to access a protected endpoint
            $response = $this->withHeaders([
                'Authorization' => "Bearer $token",
            ])->getJson('/api/v1/users/me');

            $response->assertStatus(200)
                ->assertJson([
                    'user' => [
                        'id' => $userId,
                        'email' => 'john@example.com',
                        'name' => 'John Doe',
                    ],
                ]);
        });

        test('trims whitespace from name and email', function () {
            $response = $this->postJson('/api/v1/register', [
                'name' => '  John Doe  ',
                'email' => '  john@example.com  ',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

            $response->assertStatus(201)
                ->assertJson([
                    'user' => [
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                    ],
                ]);
        });
    });
});
