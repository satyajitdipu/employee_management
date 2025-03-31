<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Support\Str;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon("heroicon-o-pencil-square"),
            Actions\Action::make('view_passport_photo')
                ->label('View Passport Photo')
                ->visible(function () {
                    return $this->record->hasMedia('passport_photo');
                })
                ->action(function () {
                    return $this->record;
                })
                ->icon("heroicon-o-photo")
                ->modalSubmitAction(null)
                ->modalContent(function () {
                    return Str::viewFromString('<div style="text-align:center;"><img src="{{ $img_url }}" alt="{{ $img_alt }}" style="display: inline-block; margin: auto; padding: 5px; border: 1px solid #CCC;" /></div>', [
                        "img_url" => Storage::temporaryUrl($this->record->getFirstMediaPath('passport_photo', 'large-preview'), now()->addMinutes(5)),
                        "img_alt" => $this->record->full_name,
                    ]);
                }),
            Actions\Action::make('download_identity_card')
                ->label('Download Identity card')
                ->visible(function () {
                    return $this->record->hasMedia('identity_card');
                })
                ->openUrlInNewTab()
                ->extraAttributes([
                    'target' => "_blank",
                ])
                ->icon('heroicon-o-arrow-down-tray')
                ->url(function () {
                    return $this->record->getFirstMedia('identity_card')->getUrl();
                }),
            Actions\Action::make('regenerate_identity_card')
                ->label('Regenerate Identity Card')
                ->visible(function () {
                    return $this->record->hasMedia('employee_photo') && $this->record->hasMedia('identity_card');
                })
                ->action(function () {
                    $employee = $this->record;
                    $employee->clearMediaCollection('passport_photo');
                    $employee->clearMediaCollection('identity_card');
                    $employee_photo = $employee->getFirstMedia('employee_photo');
                    event(new \Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent($employee_photo));
                    Log::info("ID Card Regeneration Queued ...");
                })
                ->icon("heroicon-o-arrow-path")
                ->requiresConfirmation(),
        ];
    }
}
