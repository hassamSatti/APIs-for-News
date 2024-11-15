<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{

    protected $model = News::class; 
    public function definition()
    {
        return [
            'source' => $this->faker->word(),
            'author' => $this->faker->name(),
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'description' => $this->faker->text(),
            'published_at' => $this->faker->dateTime(),
        ];
    }
}
