<?php

namespace Tests\Feature\Api\V1\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

describe('LoginController', function () {
    beforeEach(function () {
        RateLimiter::clear('login.test@example.com|127.0.0.1');
    });

    describe('Login', function () {
        test('authenticates user with valid credentials', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('Password123!'),
            ]);

            $response = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'Password123!',
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                ])
                ->assertJson([
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => $user->name,
                    ],
                ]);

            expect($response->json('token'))->toBeString()->not->toBeEmpty();
        });

        test('returns validation error for missing email', function () {
            $response = $this->postJson('/api/v1/login', [
                'password' => 'Password123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('returns validation error for missing password', function () {
            $response = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        test('returns validation error for invalid email format', function () {
            $response = $this->postJson('/api/v1/login', [
                'email' => 'invalid-email',
                'password' => 'Password123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('returns authentication error for non-existent user', function () {
            $response = $this->postJson('/api/v1/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'Password123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email'])
                ->assertJsonFragment([
                    'email' => [trans('auth.failed')],
                ]);
        });

        test('returns authentication error for incorrect password', function () {
            User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('CorrectPassword123!'),
            ]);

            $response = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'WrongPassword123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email'])
                ->assertJsonFragment([
                    'email' => [trans('auth.failed')],
                ]);
        });

        test('implements rate limiting after failed attempts', function () {
            User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('CorrectPassword123!'),
            ]);

            // Make 5 failed login attempts
            for ($i = 0; $i < 5; $i++) {
                $this->postJson('/api/v1/login', [
                    'email' => 'test@example.com',
                    'password' => 'WrongPassword123!',
                ]);
            }

            // 6th attempt should be rate limited
            $response = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'WrongPassword123!',
            ]);
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);

            $errorMessage = $response->json('errors.email.0');
            expect($errorMessage)->toContain('Too many login attempts');
        });

        test('clears rate limit after successful login', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('CorrectPassword123!'),
            ]);

            // Make some failed attempts
            for ($i = 0; $i < 3; $i++) {
                $this->postJson('/api/v1/login', [
                    'email' => 'test@example.com',
                    'password' => 'WrongPassword123!',
                ]);
            }

            // Successful login should clear the rate limit
            $response = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'CorrectPassword123!',
            ]);

            $response->assertStatus(200);

            // Next failed attempt should not be immediately rate limited
            $response = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'WrongPassword123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email'])
                ->assertJsonFragment([
                    'email' => [trans('auth.failed')],
                ]);
        });

        test('returns user data without sensitive information', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('Password123!'),
            ]);

            $response = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'Password123!',
            ]);

            $response->assertStatus(200)
                ->assertJsonMissing(['password'])
                ->assertJsonStructure([
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                ]);
        });

        test('token is valid for API access', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('Password123!'),
            ]);

            $loginResponse = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'Password123!',
            ]);

            $token = $loginResponse->json('token');

            // Use the token to access a protected endpoint
            $response = $this->withHeaders([
                'Authorization' => "Bearer $token",
            ])->getJson('/api/v1/users/me');

            $response->assertStatus(200)
                ->assertJson([
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                    ],
                ]);
        });
    });
});
