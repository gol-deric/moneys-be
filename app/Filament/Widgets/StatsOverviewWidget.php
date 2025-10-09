<?php

namespace App\Filament\Widgets;

use App\Models\Notification;
use App\Models\Subscription;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $guestUsers = User::where('is_guest', true)->count();
        $regularUsers = $totalUsers - $guestUsers;

        $totalSubscriptions = Subscription::count();
        $activeSubscriptions = Subscription::where('is_cancelled', false)->count();
        $cancelledSubscriptions = $totalSubscriptions - $activeSubscriptions;

        $unreadNotifications = Notification::where('is_read', false)->count();

        $totalRevenue = Subscription::where('is_cancelled', false)->sum('price');

        return [
            Stat::make('Total Users', $totalUsers)
                ->description("{$regularUsers} regular, {$guestUsers} guests")
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Active Subscriptions', $activeSubscriptions)
                ->description("{$cancelledSubscriptions} cancelled")
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('primary'),

            Stat::make('Monthly Revenue', '$' . number_format($totalRevenue, 2))
                ->description('From active subscriptions')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),

            Stat::make('Unread Notifications', $unreadNotifications)
                ->description('Pending user notifications')
                ->descriptionIcon('heroicon-m-bell')
                ->color($unreadNotifications > 0 ? 'danger' : 'success'),
        ];
    }
}
