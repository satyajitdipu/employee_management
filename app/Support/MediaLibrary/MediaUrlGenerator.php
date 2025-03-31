<?php

namespace App\Support\MediaLibrary;

use Carbon\Carbon;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;
use Illuminate\Support\Facades\Storage;
use DateTime;

class MediaUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        // Get the default URL
        $defaultUrl = parent::getUrl();

        // Check if we are using the s3 disk
        if ($this->media->disk === 's3') {
            // Generate a pre-signed URL for the S3 object
            $path = $this->pathGenerator->getPath($this->media) . $this->media->getAttribute('file_name');
            return Storage::disk('s3')->temporaryUrl($path, Carbon::now()->addMinutes(10));
        }

        return $defaultUrl;
    }
}
