<?php

namespace Database\Seeders\Production;

use Illuminate\Database\Seeder;
use Backpack\Settings\app\Models\Setting;

class LpColorSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * php artisan db:seed --class="Database\\Seeders\\Production\\LpColorSettingSeeder"
     * 
     * @return void
     */
    public function run()
    {
        $result = Setting::firstOrCreate(
            ['key' => 'lp_color'],
            [
                'key' => 'lp_color',
                'name' => 'LP Color',
                'description' => 'Configure lose percentage color.',
                'value' => json_encode([
                    [
                        'from' => '-100',
                        'to' => '-1',
                        'color' => 'red',
                    ],
                    [
                        'from' => '0',
                        'to' => '',
                        'color' => 'gray',
                    ],
                    [
                        'from' => '1',
                        'to' => '100',
                        'color' => 'green',
                    ],
                ]),
                'field' => json_encode([
                    'name' => 'value',
                    'label' => 'Value',
                    'type' => 'repeatable',
                    'fields' => [
                        [
                            'name' => 'from',
                            'label' => 'From',
                            'type' => 'number',
                            'wrapper' => ['class' => 'form-group col-md-6'],
                            'hint' => 'Start of the percentage range (e.g. 0 or -100)'
                        ],
                        [
                            'name' => 'to',
                            'label' => 'To',
                            'type' => 'number',
                            'wrapper' => ['class' => 'form-group col-md-6'],
                            'hint' => 'End of the percentage range (e.g. 50 or -1)'
                        ],
                        [
                            'name' => 'color',
                            'label' => 'Color',
                            'type' => 'text',
                            'wrapper' => ['class' => 'form-group col-md-6'],
                            'hint' => 'Color name (e.g. red)'
                        ],
                    ],
                ]),
                'active' => 1,
            ],
        );

        if (!$result) {
            $this->command->info("Insert LP Setting failed.");
            return;
        }


        $this->command->info('Inserted LP Setting record successfully.');
    }
}
