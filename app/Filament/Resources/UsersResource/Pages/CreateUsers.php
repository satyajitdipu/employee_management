<?php

namespace App\Filament\Resources\UsersResource\Pages;

use App\Filament\Resources\UsersResource;

use App\Support\Webhook;
use Filament\Resources\Pages\CreateRecord;

class CreateUsers extends CreateRecord
{


    protected static string $resource = UsersResource::class;


    protected function afterCreate()
    {
        $this->record->load('roles');
        Webhook::sendWebhookRequests($this->record);
    }
}
