<?php

namespace App\Filament\Resources\FlowsResource\Pages;

use App\Filament\Resources\FlowsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFlows extends ManageRecords
{
    protected static string $resource = FlowsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
