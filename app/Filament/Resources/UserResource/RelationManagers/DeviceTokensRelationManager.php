<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeviceTokensRelationManager extends RelationManager
{
    protected static string $relationship = 'deviceTokens';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('fcm_token')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('device_name')
            ->columns([
                Tables\Columns\TextColumn::make('device_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'android' => 'success',
                        'ios' => 'info',
                        'web' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('device_name')
                    ->limit(30),

                Tables\Columns\TextColumn::make('app_version'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->dateTime()
                    ->since(),

                Tables\Columns\TextColumn::make('fcm_token')
                    ->label('FCM Token')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->fcm_token)
                    ->copyable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->headerActions([
                // Don't allow manual creation - tokens should come from API
            ])
            ->actions([
                Tables\Actions\Action::make('deactivate')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->deactivate())
                    ->visible(fn ($record) => $record->is_active),

                Tables\Actions\Action::make('activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->activate())
                    ->visible(fn ($record) => !$record->is_active),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('last_used_at', 'desc');
    }
}
