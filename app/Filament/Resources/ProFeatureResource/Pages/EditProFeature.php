<?php

namespace App\Filament\Resources\ProFeatureResource\Pages;

use App\Filament\Resources\ProFeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProFeature extends EditRecord
{
    protected static string $resource = ProFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
