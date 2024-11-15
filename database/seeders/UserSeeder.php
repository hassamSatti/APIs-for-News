<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if the user already exists
        $user = User::where('email', 'test@example.com')->first();
        if ($user) { 
            $this->command->info("User with email 'test@example.com' already exists.");
        } else { 
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('passcode'),
            ]); 
            $this->command->info("Test User created:");
            $this->command->info("Email: test@example.com");
            $this->command->info("Password: passcode");
        }
    }
}
