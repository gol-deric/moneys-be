<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'email' => 'admin@moneys.com',
            'password' => Hash::make('password'),
            'full_name' => 'Admin User',
            'is_guest' => false,
            'subscription_tier' => 'enterprise',
            'notifications_enabled' => true,
            'email_notifications' => true,
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@moneys.com');
        $this->command->info('Password: password');
    }
}
