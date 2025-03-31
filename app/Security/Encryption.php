<?php

namespace App\Security;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class Encryption
{

    public static function generateClientSecret(array $data)
    {
        $jsonPayload = json_encode($data);
        $key = config('app.secret_key');

        $encryptedData = Crypt::encrypt($jsonPayload, $key);
        return $encryptedData;
    }

    public static function decryptClientSecret(string $encryptedData)
    {
        $key = config('app.secret_key');
        $jsonPayload = Crypt::decrypt($encryptedData, $key);

        return json_decode($jsonPayload, true);
    }
}
