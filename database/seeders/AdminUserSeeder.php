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
        User::updateOrCreate(
            ['email' => 'admin@moneys.com'],
            [
                'password' => Hash::make('password'),
                'full_name' => 'Admin User',
                'is_guest' => false,
                'is_admin' => true,
                'subscription_tier' => 'pro',
                'notifications_enabled' => true,
                'email_notifications' => true,
            ]
        );

        $this->command->info('Admin user created/updated successfully!');
        $this->command->info('Email: admin@moneys.com');
        $this->command->info('Password: password');
        $this->command->info('Admin access: YES');
    }
}
