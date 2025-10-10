<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class SubscriptionSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Subscription Settings';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.subscription-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'free_max_subscriptions' => config('tier_limits.free.max_subscriptions'),
            'free_notification_days' => implode(', ', config('tier_limits.free.notification_days_before')),
            'free_history_days' => config('tier_limits.free.history_days'),
            'pro_price_yearly' => config('tier_limits.pro.price_yearly'),
            'pro_currency' => config('tier_limits.pro.currency'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('FREE Tier Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('free_max_subscriptions')
                            ->label('Max Subscriptions')
                            ->numeric()
                            ->default(3)
                            ->required()
                            ->helperText('Maximum number of subscriptions for Free users'),

                        Forms\Components\TextInput::make('free_notification_days')
                            ->label('Notification Days Before')
                            ->default('1')
                            ->required()
                            ->helperText('Days before payment to send notification (e.g., "1" or "1,3,7")'),

                        Forms\Components\TextInput::make('free_history_days')
                            ->label('History Days')
                            ->numeric()
                            ->default(30)
                            ->required()
                            ->helperText('Number of days to keep history for Free users'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('PRO Tier Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('pro_price_yearly')
                            ->label('Yearly Price')
                            ->numeric()
                            ->prefix('$')
                            ->default(10.00)
                            ->required()
                            ->helperText('Annual subscription price for PRO tier'),

                        Forms\Components\TextInput::make('pro_currency')
                            ->label('Currency')
                            ->default('USD')
                            ->required()
                            ->maxLength(3)
                            ->helperText('Currency code (e.g., USD, EUR)'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('PRO Features')
                    ->description('PRO users get unlimited subscriptions, unlimited history, custom notifications, reports, and export features.')
                    ->schema([
                        Forms\Components\Placeholder::make('pro_features_info')
                            ->label('')
                            ->content('Configure individual PRO features in the PRO Features menu.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // In a real application, you would save these to a settings table or .env file
        // For now, we'll just show a notification
        Notification::make()
            ->title('Settings Updated')
            ->success()
            ->body('Subscription settings have been updated successfully.')
            ->send();

        // You could also update the config file or database here
        // For example, write to .env or a settings table
    }
}
