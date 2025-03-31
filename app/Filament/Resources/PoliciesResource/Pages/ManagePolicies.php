<?php

namespace App\Filament\Resources\PoliciesResource\Pages;

use App\Filament\Resources\PoliciesResource;
use App\Models\Stages;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;
use Closure;


class ManagePolicies extends ManageRecords
{
    protected static string $resource = PoliciesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()


        ];
    }
}

