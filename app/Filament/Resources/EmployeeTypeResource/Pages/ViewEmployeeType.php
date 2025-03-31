<?php

namespace App\Filament\Resources\EmployeeTypeResource\Pages;

use App\Filament\Resources\EmployeeTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeType extends ViewRecord
{
    protected static string $resource = EmployeeTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
