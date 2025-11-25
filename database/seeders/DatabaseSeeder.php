<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Production\IgnoreAccountSettingSeeder;
use Database\Seeders\Production\LpColorSettingSeeder;
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
            // Default user
            DefaultUserSeeder::class,

            // Data
            ChannelSeeder::class,
            BetSeeder::class,
            MasterSeeder::class,

            // Setting
            LpColorSettingSeeder::class,
            IgnoreAccountSettingSeeder::class,
        ]);
    }
}
