<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProFeatureResource\Pages;
use App\Filament\Resources\ProFeatureResource\RelationManagers;
use App\Models\ProFeature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProFeatureResource extends Resource
{
    protected static ?string $model = ProFeature::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'PRO Features';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Feature Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Feature Name'),

                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->label('Feature Key')
                            ->helperText('Unique identifier (e.g., custom_notifications, export_data)'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->label('Description'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->default(true)
                            ->helperText('Enable or disable this feature'),

                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->label('Additional Price')
                            ->helperText('Extra cost for this feature (0 = included in PRO)'),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Sort Order')
                            ->helperText('Display order in feature list'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->label('#')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Feature Name')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->label('Key'),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->wrap()
                    ->label('Description'),

                Tables\Columns\IconColumn::make('is_enabled')
                    ->boolean()
                    ->label('Enabled')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->label('Extra Price')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label('Enabled')
                    ->placeholder('All features')
                    ->trueLabel('Enabled only')
                    ->falseLabel('Disabled only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListProFeatures::route('/'),
            'create' => Pages\CreateProFeature::route('/create'),
            'edit' => Pages\EditProFeature::route('/{record}/edit'),
        ];
    }
}
