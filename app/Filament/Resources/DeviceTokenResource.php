<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceTokenResource\Pages;
use App\Filament\Resources\DeviceTokenResource\RelationManagers;
use App\Models\DeviceToken;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeviceTokenResource extends Resource
{
    protected static ?string $model = DeviceToken::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationGroup = 'Communications';

    protected static ?string $navigationLabel = 'Device Tokens';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                Forms\Components\TextInput::make('device_type')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('device_name')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('app_version')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\DateTimePicker::make('last_used_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('device_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'android' => 'success',
                        'ios' => 'info',
                        'web' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('device_name')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('app_version')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('device_type')
                    ->options([
                        'android' => 'Android',
                        'ios' => 'iOS',
                        'web' => 'Web',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All tokens')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->defaultSort('last_used_at', 'desc')
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
            'index' => Pages\ListDeviceTokens::route('/'),
            'create' => Pages\CreateDeviceToken::route('/create'),
            'edit' => Pages\EditDeviceToken::route('/{record}/edit'),
        ];
    }
}
