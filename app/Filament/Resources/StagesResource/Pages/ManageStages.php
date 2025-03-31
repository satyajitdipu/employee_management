<?php

namespace App\Filament\Resources\StagesResource\Pages;

use Closure;
use Filament\Pages\Actions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\StagesResource;
use App\Models\Stages;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Factories\Relationship;
use Ramsey\Uuid\Type\Integer;

class ManageStages extends ManageRecords
{
    protected static string $resource = StagesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()

        ];
    }
}

