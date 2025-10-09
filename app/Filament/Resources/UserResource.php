<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_guest')
                    ->default(false),
                Forms\Components\Select::make('subscription_tier')
                    ->options([
                        'free' => 'Free',
                        'premium' => 'Premium',
                        'enterprise' => 'Enterprise',
                    ])
                    ->default('free')
                    ->required(),
                Forms\Components\Select::make('theme')
                    ->options([
                        'light' => 'Light',
                        'dark' => 'Dark',
                    ])
                    ->default('light')
                    ->required(),
                Forms\Components\Toggle::make('notifications_enabled')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_guest')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscription_tier')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'free' => 'gray',
                        'premium' => 'warning',
                        'enterprise' => 'success',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->counts('subscriptions')
                    ->label('Subscriptions')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subscription_tier')
                    ->options([
                        'free' => 'Free',
                        'premium' => 'Premium',
                        'enterprise' => 'Enterprise',
                    ]),
                Tables\Filters\Filter::make('is_guest')
                    ->query(fn (Builder $query): Builder => $query->where('is_guest', true))
                    ->label('Guest Users'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
