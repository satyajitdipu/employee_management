<?php

namespace App\Filament\Resources\EmployeeTypeResource\Pages;

use App\Filament\Resources\EmployeeTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeType extends CreateRecord
{
    protected static string $resource = EmployeeTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
