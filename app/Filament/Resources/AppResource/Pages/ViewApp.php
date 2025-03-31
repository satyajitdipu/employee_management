<?php

namespace App\Filament\Resources\AppResource\Pages;

use App\Models\App;
use Filament\Actions;
use Filament\Forms\Form;
use App\Models\Organization;
use Filament\Forms\Components\Section;
use App\Filament\Resources\AppResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\Placeholder;
use Illuminate\Contracts\Support\Htmlable;

class ViewApp extends EditRecord
{
    protected static string $resource = AppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
            ->url(route('filament.app.resources.apps.edit',['record' => $this->record->id])),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function getBreadcrumb(): string
    {
        return 'View';
    }

    public function getTitle(): string | Htmlable
    {
        return 'View App'; 
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('App Details')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('name')
                            ->content(fn ($state) => $state),
                        Placeholder::make('description')
                            ->content(fn ($state) => $state),
                        Placeholder::make('url')
                            ->content(fn ($state) => $state),
                        Placeholder::make('organization_id')
                            ->content(fn ($state) => Organization::where('id',$state)->first()->name)
                    ])
            ]);
    }
}
