<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\News;
use App\Models\UserNewsPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserNewsPreferenceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_user_preferences_when_preferences_exist()
    {  
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
 
        $preference = UserNewsPreference::create([
            'user_id' => $user->id,
            'authors' => ['Author1', 'Author2'],
            'sources' => ['Source1', 'Source2']
        ]);
        $response = $this->getJson('/api/preferences')->assertStatus(200)->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'authors',
                'sources',
            ]
        ]);
        $response->assertJson([
            'data' => [
                'authors' => ['Author1', 'Author2'],
                'sources' => ['Source1', 'Source2'],
            ]
        ]);
    }
     /** @test */
     public function it_returns_404_if_no_preferences_found()
     { 
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum'); 
        $response = $this->getJson('/api/preferences'); 
        $response->assertStatus(404)
                 ->assertJson([
                     'message' => 'No preferences found for this user'
                 ]);
     }
     /** @test */
    public function it_returns_news_based_on_user_preferences()
    {
        $user = User::factory()->create();
        UserNewsPreference::create([
            'user_id' => $user->id,
            'authors' => ['Author1', 'Author2'],
            'sources' => ['Source1', 'Source2'],
        ]);

        News::factory()->create(['author' => 'Author1', 'source' => 'Source1']);
        News::factory()->create(['author' => 'Author2', 'source' => 'Source2']);
        News::factory()->create(['author' => 'Author3', 'source' => 'Source3']); // Non-matching

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/personalizedNews');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success', 'message', 'data' => ['data']
            ]);

        // Assert news matching preferences
        $response->assertJsonFragment(['author' => 'Author1']);
        $response->assertJsonFragment(['source' => 'Source1']);
        $response->assertJsonFragment(['author' => 'Author2']);
        $response->assertJsonFragment(['source' => 'Source2']);

        // Ensure non-matching news is not in the response
        $response->assertJsonMissing(['author' => 'Author3']);
    }
}
