<?php

namespace App\Support;

use App\Jobs\SendWebhookRequest;
use App\Settings\CookieSettings;

class Webhook
{
    public static function sendWebhookRequests($model)
    {
        $webhooks = app(CookieSettings::class)->webhooks;
        foreach ($webhooks as $webhook) {
            if ($webhook['status']) {
                SendWebhookRequest::dispatch($model, $webhook);
            }
        }
        return;
    }
}
