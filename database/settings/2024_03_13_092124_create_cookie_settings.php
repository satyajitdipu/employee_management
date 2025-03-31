<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('cookie.webhooks', [
            [
                "name" => "cranberry_punch",
                "url" => "http://127.0.0.1:8000/api/webhook",
                "secret-key" => "tj1LVevl",
                "secret-header" => "FPn0OI0Z",
                "status" => false
            ]

        ]);
    }

    public function down()
    {
        $this->migrator->delete('cookie.webhooks');
    }
};
