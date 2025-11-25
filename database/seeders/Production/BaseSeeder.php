<?php

namespace Database\Seeders\Production;

use Database\Seeders\Production\DefaultUserSeeder;
use Database\Seeders\Production\IgnoreAccountSettingSeeder;
use Database\Seeders\Production\LpColorSettingSeeder;
use Illuminate\Database\Seeder;

class BaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * php artisan db:seed --class=Database\\Seeders\\Production\\BaseSeeder
   */
  public function run(): void
  {
    $this->call([
      // Data
      DefaultUserSeeder::class,

      // Setting
      LpColorSettingSeeder::class,
      IgnoreAccountSettingSeeder::class,
    ]);
  }
}
