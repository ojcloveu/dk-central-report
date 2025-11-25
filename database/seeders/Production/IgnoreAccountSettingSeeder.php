<?php

namespace Database\Seeders\Production;

use Illuminate\Database\Seeder;
use Backpack\Settings\app\Models\Setting;

class IgnoreAccountSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * php artisan db:seed --class="Database\\Seeders\\Production\\IgnoreAccountSettingSeeder"
     * 
     * @return void
     */
    public function run()
    {
        $result = Setting::firstOrCreate(
            ['key' => 'ignore_account'],
            [
                'key' => 'ignore_account',
                'name' => 'Ignore Account',
                'description' => 'Configure accounts to ignore in bet reports.',
                'value' => '',
                'field' => json_encode([
                    'name' => 'value',
                    'label' => 'Accounts to Ignore',
                    'type' => 'textarea',
                    'hint' => 'Enter account names separated by commas (e.g., account1, account2, account3)',
                ]),
                'active' => 1,
            ],
        );

        if (!$result) {
            $this->command->info("Insert Ignore Account Setting failed.");
            return;
        }


        $this->command->info('Inserted Ignore Account Setting record successfully.');
    }
}
