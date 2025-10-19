<?php

namespace App\Filament\Widgets;

use App\Models\Subscription;
use App\Models\User;
use App\Models\UserDevice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success')
                ->chart([7, 12, 15, 18, 22, 25, User::count()]),

            Stat::make('Guest Users', User::where('is_guest', true)->count())
                ->description('Guest accounts')
                ->descriptionIcon('heroicon-m-user')
                ->color('warning'),

            Stat::make('Active Devices', UserDevice::where('is_active', true)->count())
                ->description('Connected devices')
                ->descriptionIcon('heroicon-m-device-phone-mobile')
                ->color('info'),

            Stat::make('Active Subscriptions', Subscription::where('status', 'active')->count())
                ->description('Current subscriptions')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('success'),
        ];
    }
}
