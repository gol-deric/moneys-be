<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Filament\Resources\SubscriptionResource\RelationManagers;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Subscription Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'full_name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('icon_url')
                    ->maxLength(500)
                    ->label('Icon URL'),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
                Forms\Components\TextInput::make('currency_code')
                    ->required()
                    ->maxLength(3)
                    ->default('USD'),
                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->default(now()),
                Forms\Components\TextInput::make('billing_cycle_count')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(1),
                Forms\Components\Select::make('billing_cycle_period')
                    ->options([
                        'day' => 'Day',
                        'month' => 'Month',
                        'quarter' => 'Quarter',
                        'year' => 'Year',
                    ])
                    ->default('month')
                    ->required(),
                Forms\Components\TextInput::make('category')
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_cancelled')
                    ->default(false),
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money(fn ($record) => $record->currency_code)
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_cancelled')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(fn () => Subscription::distinct()->pluck('category', 'category')->filter()->toArray()),
                Tables\Filters\Filter::make('is_cancelled')
                    ->query(fn (Builder $query): Builder => $query->where('is_cancelled', true))
                    ->label('Cancelled'),
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
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
