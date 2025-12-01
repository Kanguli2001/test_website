<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Chirp;
use App\Models\User;

class ChirpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a feww sample users and chirps for testing

        $users = User::count() < 3 ? 
            collect([
                User::create([
                    "name"=> "Alex Developer",
                    "email"=> "alex@example.com",
                    "password"=> bcrypt("password123"),
                ]),
                User::create([
                    "name"=> "Jamie Coder",
                    "email"=> "james@example.com",
                    "password"=> bcrypt("password456"),
                ]),
                User::create([
                    "name"=> "Sam Programmer",
                    "email"=> "sam@example.com",
                    "password"=> bcrypt("password789"),
                ]),
                User::create([
                    "name"=> "Taylor Hacker",
                    "email"=> "taylor@example.com",
                    "password"=> bcrypt("password000"),
                ]),
            ])
            :User::take(4)->get();

        $chirps = [
            'Just discovered Laravel - where has this been all my life? 🚀',
            'Building something cool with Chirper today!',
            'Laravel\'s Eloquent ORM is pure magic ✨',
            'Deployed my first app with Laravel Cloud. So smooth!',
            'Who else is loving Blade components?',
            'Friday deploys with Laravel? No problem! 😎',
        ];


        //Create chirps for random users
        foreach ($chirps as $message) {
            $users->random()->chirps()->create([
                'message' => $message,
                'created_at' => now()->subMinutes(rand(5, 1440)),
                'updated_at' => now()->subMinutes(rand(5, 1440)),
            ]);
        }
    }
}
