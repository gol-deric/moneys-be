<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@moneys.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $this->command->info('Default admin user created!');
        $this->command->info('Email: admin@moneys.com');
        $this->command->info('Password: password');
    }
}
