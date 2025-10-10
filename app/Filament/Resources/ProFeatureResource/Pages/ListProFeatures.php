<?php

namespace App\Filament\Resources\ProFeatureResource\Pages;

use App\Filament\Resources\ProFeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProFeatures extends ListRecords
{
    protected static string $resource = ProFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
