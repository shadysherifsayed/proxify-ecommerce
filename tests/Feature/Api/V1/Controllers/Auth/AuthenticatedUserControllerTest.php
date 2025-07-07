<?php

namespace Tests\Feature\Api\V1\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('AuthenticatedUserController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    });

    describe('GET /api/v1/users/me', function () {
        test('returns authenticated user data', function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/v1/users/me');

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
                ])
                ->assertJson([
                    'user' => [
                        'id' => $this->user->id,
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                    ],
                ]);
        });

        test('does not return sensitive information', function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/v1/users/me');

            $response->assertStatus(200)
                ->assertJsonMissing(['password'])
                ->assertJsonMissing(['remember_token']);
        });

        test('requires authentication', function () {
            $response = $this->getJson('/api/v1/users/me');

            $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.',
                ]);
        });

        test('returns unauthorized for invalid token', function () {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer invalid-token',
            ])->getJson('/api/v1/users/me');

            $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.',
                ]);
        });

        test('works with valid Bearer token', function () {
            $token = $this->user->createToken('test-token');

            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token->plainTextToken}",
            ])->getJson('/api/v1/users/me');

            $response->assertStatus(200)
                ->assertJson([
                    'user' => [
                        'id' => $this->user->id,
                        'email' => $this->user->email,
                    ],
                ]);
        });

        test('returns fresh user data from database', function () {
            Sanctum::actingAs($this->user);

            // Update user in database
            $this->user->update(['name' => 'Jane Smith']);

            $response = $this->getJson('/api/v1/users/me');

            $response->assertStatus(200)
                ->assertJson([
                    'user' => [
                        'id' => $this->user->id,
                        'name' => 'Jane Smith',
                        'email' => 'john@example.com',
                    ],
                ]);
        });

        test('includes all expected user fields', function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/v1/users/me');

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
                ]);

            $userData = $response->json('user');

            expect($userData['id'])->toBeInt();
            expect($userData['name'])->toBeString();
            expect($userData['email'])->toBeString();
            expect($userData['created_at'])->toBeString();
            expect($userData['updated_at'])->toBeString();
        });

        test('works after user updates their profile', function () {
            Sanctum::actingAs($this->user);

            // Simulate profile update
            $this->user->update([
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);

            $response = $this->getJson('/api/v1/users/me');

            $response->assertStatus(200)
                ->assertJson([
                    'user' => [
                        'id' => $this->user->id,
                        'name' => 'Updated Name',
                        'email' => 'updated@example.com',
                    ],
                ]);
        });
    });
});
