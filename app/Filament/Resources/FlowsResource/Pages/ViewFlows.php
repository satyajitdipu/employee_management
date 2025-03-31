<?php

namespace App\Filament\Resources\FlowsResource\Pages;


use App\Filament\Resources\FlowsResource;

use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFlows extends ViewRecord
{
    protected static string $resource = FlowsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
