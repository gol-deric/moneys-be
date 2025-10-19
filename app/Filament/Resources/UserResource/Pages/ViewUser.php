<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('User Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('full_name')
                                    ->label('Full Name')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-o-user')
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable()
                                    ->placeholder('Guest User'),
                                Infolists\Components\TextEntry::make('id')
                                    ->label('User ID')
                                    ->copyable()
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Small),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Infolists\Components\Section::make('Account Details')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('is_guest')
                                    ->label('Account Type')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? 'Guest' : 'Regular')
                                    ->color(fn ($state) => $state ? 'warning' : 'success')
                                    ->icon(fn ($state) => $state ? 'heroicon-o-user' : 'heroicon-o-user-group'),
                                Infolists\Components\TextEntry::make('is_active')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive')
                                    ->color(fn ($state) => $state ? 'success' : 'danger')
                                    ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
                                Infolists\Components\TextEntry::make('language')
                                    ->label('Language')
                                    ->badge()
                                    ->icon('heroicon-o-language'),
                                Infolists\Components\TextEntry::make('currency')
                                    ->label('Currency')
                                    ->badge()
                                    ->icon('heroicon-o-currency-dollar'),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Infolists\Components\Section::make('Activity')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Registered')
                                    ->dateTime()
                                    ->icon('heroicon-o-calendar')
                                    ->since(),
                                Infolists\Components\TextEntry::make('last_logged_in')
                                    ->label('Last Login')
                                    ->dateTime()
                                    ->icon('heroicon-o-clock')
                                    ->since()
                                    ->placeholder('Never'),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime()
                                    ->icon('heroicon-o-arrow-path')
                                    ->since(),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Infolists\Components\Section::make('Devices')
                    ->description('All devices registered to this user')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('devices')
                            ->label('')
                            ->schema([
                                Infolists\Components\Grid::make(6)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('device_name')
                                            ->label('Device Name')
                                            ->icon('heroicon-o-device-phone-mobile')
                                            ->weight(FontWeight::Bold),
                                        Infolists\Components\TextEntry::make('device_type')
                                            ->label('Type')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'android' => 'success',
                                                'ios' => 'gray',
                                                'web' => 'info',
                                                default => 'warning',
                                            })
                                            ->icon(fn (string $state): string => match ($state) {
                                                'android' => 'heroicon-o-device-phone-mobile',
                                                'ios' => 'heroicon-o-device-phone-mobile',
                                                'web' => 'heroicon-o-computer-desktop',
                                                default => 'heroicon-o-question-mark-circle',
                                            }),
                                        Infolists\Components\TextEntry::make('device_id')
                                            ->label('Device ID')
                                            ->limit(20)
                                            ->copyable()
                                            ->tooltip(fn ($state) => $state),
                                        Infolists\Components\TextEntry::make('is_active')
                                            ->label('Status')
                                            ->badge()
                                            ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive')
                                            ->color(fn ($state) => $state ? 'success' : 'danger')
                                            ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
                                        Infolists\Components\TextEntry::make('created_at')
                                            ->label('Added')
                                            ->dateTime('M d, Y')
                                            ->since(),
                                        Infolists\Components\TextEntry::make('fcm_token')
                                            ->label('FCM Token')
                                            ->placeholder('Not set')
                                            ->limit(15)
                                            ->tooltip(fn ($state) => $state ?? 'No FCM token'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->icon('heroicon-o-device-phone-mobile')
                    ->collapsible(),

                Infolists\Components\Section::make('Subscriptions')
                    ->description('All subscriptions for this user')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('subscriptions')
                            ->label('')
                            ->schema([
                                Infolists\Components\Grid::make(6)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Subscription')
                                            ->icon('heroicon-o-credit-card')
                                            ->weight(FontWeight::Bold)
                                            ->color('primary'),
                                        Infolists\Components\TextEntry::make('price')
                                            ->label('Price')
                                            ->money(fn ($record) => $record->currency ?? 'USD')
                                            ->weight(FontWeight::Bold),
                                        Infolists\Components\TextEntry::make('billing_cycle')
                                            ->label('Billing')
                                            ->badge()
                                            ->color('info'),
                                        Infolists\Components\TextEntry::make('status')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'active' => 'success',
                                                'cancelled' => 'danger',
                                                'expired' => 'warning',
                                                'pending' => 'info',
                                                default => 'gray',
                                            }),
                                        Infolists\Components\TextEntry::make('start_date')
                                            ->label('Start Date')
                                            ->date('M d, Y'),
                                        Infolists\Components\TextEntry::make('end_date')
                                            ->label('End Date')
                                            ->date('M d, Y')
                                            ->placeholder('No end date'),
                                        Infolists\Components\TextEntry::make('description')
                                            ->label('Description')
                                            ->placeholder('No description')
                                            ->columnSpanFull()
                                            ->limit(100),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->icon('heroicon-o-credit-card')
                    ->collapsible(),
            ]);
    }
}
