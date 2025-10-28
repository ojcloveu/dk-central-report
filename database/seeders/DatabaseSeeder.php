<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * 
     * php artisan db:seed --class="Database\\Seeders\\DatabaseSeeder"
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Developer',
            'email' => 'admin@admin.com',
            'password' => bcrypt('zaq1ZAQ!'),
        ]);

        $this->call([
            ChannelSeeder::class,
            BetSeeder::class,
        ]);
    }
}
