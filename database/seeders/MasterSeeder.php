<?php

namespace Database\Seeders;

use App\Models\Master;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * php artisan db:seed --class="Database\\Seeders\\MasterSeeder"
     */
    public function run(): void
    {
        $channels = [
            [
                'name' => 'DKDK',
                'description' => 'DKDK master',
                'is_active' => true,
            ],
            [
                'name' => 'DKAO',
                'description' => 'DKAO master',
                'is_active' => true,
            ]
        ];

        foreach ($channels as $channel) {
            Master::firstOrCreate(
                ['name' => $channel['name']],
                $channel
            );
        }
    }
}