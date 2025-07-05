<?php

namespace Tests\Feature\Api\V1\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('LogoutController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    describe('Logout', function () {
        test('logs out authenticated user successfully', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/logout');

            $response->assertStatus(204);
        });

        test('deletes current access token', function () {
            // Create a token for the user
            $token = $this->user->createToken('test-token');

            // Use the token to authenticate
            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token->plainTextToken}",
            ])->postJson('/api/v1/logout');

            $response->assertStatus(204);

            // Verify the token is deleted
            $this->assertDatabaseMissing('personal_access_tokens', [
                'id' => $token->accessToken->id,
            ]);
        });

        test('does not affect other user tokens', function () {
            // Create multiple tokens for the user
            $token1 = $this->user->createToken('token-1');
            $token2 = $this->user->createToken('token-2');

            // Create token for another user
            $otherUser = User::factory()->create();
            $otherToken = $otherUser->createToken('other-token');

            // Use token1 to logout
            $response = $this->withHeaders([
                'Authorization' => "Bearer {$token1->plainTextToken}",
            ])->postJson('/api/v1/logout');

            $response->assertStatus(204);

            // Verify only token1 is deleted
            $this->assertDatabaseMissing('personal_access_tokens', [
                'id' => $token1->accessToken->id,
            ]);

            // Verify token2 and otherToken still exist
            $this->assertDatabaseHas('personal_access_tokens', [
                'id' => $token2->accessToken->id,
            ]);
            $this->assertDatabaseHas('personal_access_tokens', [
                'id' => $otherToken->accessToken->id,
            ]);
        });

        test('requires authentication', function () {
            $response = $this->postJson('/api/v1/logout');

            $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.',
                ]);
        });

        test('returns unauthorized for invalid token', function () {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer invalid-token',
            ])->postJson('/api/v1/logout');

            $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.',
                ]);
        });

        test('can logout with different token formats', function () {
            $token = $this->user->createToken('test-token');

            // Test with Bearer prefix (case insensitive)
            $response = $this->withHeaders([
                'Authorization' => "bearer {$token->plainTextToken}",
            ])->postJson('/api/v1/logout');

            $response->assertStatus(204);
        });

        test('preserves user data after logout', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/logout');

            $response->assertStatus(204);

            // Verify user still exists in database
            $this->assertDatabaseHas('users', [
                'id' => $this->user->id,
                'email' => $this->user->email,
            ]);
        });
        test('returns no content on successful logout', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/logout');

            $response->assertStatus(204)
                ->assertNoContent();
        });
    });
});
