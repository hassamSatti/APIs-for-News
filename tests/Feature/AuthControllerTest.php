<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Carbon\Carbon; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function it_registers_a_user_successfully()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)->assertJsonStructure(['message', 'access_token', 'token_type']);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    } 
    /** @test */
    public function it_logs_in_user_successfully()
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['message', 'access_token', 'token_type']);
    }
    /** @test */ 
    public function it_logs_out_user_successfully()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/api/auth/logout')->assertStatus(200)->assertJson(['message' => 'Logged out successfully']);
    }
    /** @test */
    public function it_resets_password_successfully_with_valid_token()
    {
        $user = User::factory()->create();
        $token = Str::random(60);
        $expiresAt = Carbon::now()->addMinutes(5);
 
        $user->update([
            'password_reset_token' => $token,
            'password_reset_token_expires_at' => $expiresAt,
        ]); 

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]); 
        $response->assertStatus(200)->assertJson(['message' => 'Password reset successfully.']);

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }
}
