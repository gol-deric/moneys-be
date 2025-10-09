<?php

namespace App\Filament\Resources\PaymentPlanResource\Pages;

use App\Filament\Resources\PaymentPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentPlan extends EditRecord
{
    protected static string $resource = PaymentPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
