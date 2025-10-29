<?php

namespace Database\Seeders\Production;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Backpack\PermissionManager\app\Models\Role;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * php artisan db:seed --class=Database\\Seeders\\Production\\DefaultUserSeeder
     */
    public function run(): void
    {
        $email = 'admin@admin.com';
        $password = 'zaq1ZAQ!';

        $roleName = config('roles.developer');

        $role = Role::firstOrCreate(['name' => $roleName]);

        $user = User::firstOrCreate(
            ['email' => $email],
            [
            'name' => $roleName,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            ]
        );

        $user->assignRole($role);
    }
}
