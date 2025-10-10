<?php

namespace App\Filament\Resources\DeviceTokenResource\Pages;

use App\Filament\Resources\DeviceTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeviceTokens extends ListRecords
{
    protected static string $resource = DeviceTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
