<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
 
        foreach (range(1, 10) as $index) {
            DB::table('news')->insert([
                'source' => $faker->word, 
                'author' => $faker->name, 
                'title' => $faker->sentence,  
                'content' => $faker->paragraph, 
                'description' => $faker->text,  
                'published_at' => $faker->dateTimeThisYear,  
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
