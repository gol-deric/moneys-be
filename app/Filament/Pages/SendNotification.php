<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\DeviceToken;
use App\Services\FirebaseService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class SendNotification extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'Communications';

    protected static ?string $navigationLabel = 'Send Notification';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.send-notification';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Notification Content')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Notification title'),

                        Forms\Components\Textarea::make('body')
                            ->label('Message')
                            ->required()
                            ->rows(4)
                            ->maxLength(1000)
                            ->placeholder('Notification message'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Recipients')
                    ->schema([
                        Forms\Components\Radio::make('recipient_type')
                            ->label('Send to')
                            ->options([
                                'all' => 'All Users',
                                'specific_users' => 'Specific Users',
                                'single_user' => 'Single User',
                            ])
                            ->default('all')
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('user_id')
                            ->label('Select User')
                            ->searchable()
                            ->options(User::query()->pluck('full_name', 'id'))
                            ->visible(fn ($get) => $get('recipient_type') === 'single_user')
                            ->required(fn ($get) => $get('recipient_type') === 'single_user'),

                        Forms\Components\Select::make('user_ids')
                            ->label('Select Users')
                            ->multiple()
                            ->searchable()
                            ->options(User::query()->pluck('full_name', 'id'))
                            ->visible(fn ($get) => $get('recipient_type') === 'specific_users')
                            ->required(fn ($get) => $get('recipient_type') === 'specific_users'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Preview')
                    ->schema([
                        Forms\Components\Placeholder::make('preview')
                            ->label('')
                            ->content(function ($get) {
                                $title = $get('title') ?? 'Notification Title';
                                $body = $get('body') ?? 'Notification message will appear here...';

                                return view('filament.components.notification-preview', [
                                    'title' => $title,
                                    'body' => $body,
                                ]);
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();
        $firebaseService = app(FirebaseService::class);

        try {
            $recipientType = $data['recipient_type'];
            $deviceTokens = [];

            // Get device tokens based on recipient type
            if ($recipientType === 'all') {
                $deviceTokens = DeviceToken::active()->get();
            } elseif ($recipientType === 'single_user') {
                $deviceTokens = DeviceToken::active()
                    ->where('user_id', $data['user_id'])
                    ->get();
            } elseif ($recipientType === 'specific_users') {
                $deviceTokens = DeviceToken::active()
                    ->whereIn('user_id', $data['user_ids'])
                    ->get();
            }

            if ($deviceTokens->isEmpty()) {
                Notification::make()
                    ->title('No devices found')
                    ->warning()
                    ->body('No active device tokens found for the selected recipients.')
                    ->send();
                return;
            }

            $successCount = 0;
            $failedCount = 0;

            foreach ($deviceTokens as $deviceToken) {
                try {
                    $firebaseService->sendNotification(
                        $deviceToken->fcm_token,
                        $data['title'],
                        $data['body'],
                        []
                    );

                    $deviceToken->markAsUsed();
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $deviceToken->deactivate();
                }
            }

            // Show success notification
            Notification::make()
                ->title('Notifications Sent')
                ->success()
                ->body("Successfully sent to {$successCount} devices. Failed: {$failedCount}")
                ->send();

            // Reset form
            $this->form->fill();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->danger()
                ->body('Failed to send notifications: ' . $e->getMessage())
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('send')
                ->label('Send Notification')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->action('send'),
        ];
    }
}
