<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Database\Seeders\Production\DefaultUserSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * php artisan db:seed
     */
    public function run(): void
    {
        $this->call([
            DefaultUserSeeder::class,
            ChannelSeeder::class,
            BetSeeder::class,
        ]);
    }
}
