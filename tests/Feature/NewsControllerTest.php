<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NewsControllerTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function index_returns_paginated_news()
    { 
        $user = User::factory()->create(); 
        News::factory()->count(10)->create(); 
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/news'); 
        $response->assertStatus(200); 
        $response->assertJsonStructure([
            'success',
            'message',       
            'data' => [
                'data',
            ],
        ]);
    }

    /** @test */
    public function show_returns_single_news()
    {
        $user = User::factory()->create();
        $news = News::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/news/{$news->id}");
        $response->assertStatus(200);
        $response ->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
    }
}
