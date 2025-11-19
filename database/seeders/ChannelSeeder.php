<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Channel;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * php artisan db:seed --class="Database\\Seeders\\ChannelSeeder"
     */
    public function run(): void
    {
        $channels = [
            [
                'channel_name' => 'DK',
                'channel_description' => 'DK Channel',
                'is_active' => true,
            ],
            [
                'channel_name' => 'VN',
                'channel_description' => 'Vietnam Channel',
                'is_active' => true,
            ],
            [
                'channel_name' => 'TH',
                'channel_description' => 'Thailand Channel',
                'is_active' => true,
            ],
            [
                'channel_name' => 'PH',
                'channel_description' => 'Philippines Channel',
                'is_active' => true,
            ],
        ];

        foreach ($channels as $channel) {
            Channel::firstOrCreate(
                ['channel_name' => $channel['channel_name']],
                $channel
            );
        }
    }
}