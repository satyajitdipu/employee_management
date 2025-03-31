<?php

namespace App\Support;

use Illuminate\Support\Str as IStr;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class Str extends IStr
{
    public static function generate_fld_key($value)
    {
        return substr("fld_" . str_replace('-', '_', IStr::slug($value)), 0, 64);
    }

    public static function remove_prefix($str, $prefix)
    {
        if (substr($str, 0, strlen($prefix)) == $prefix) {
            $str = substr($str, strlen($prefix));
        }
        return $str;
    }

    public static function subscript_decimal($num)
    {
        if (!is_numeric($num)) {
            return "";
        }
        $num = number_format($num, 2, '.', '');
        $num_parts = explode('.', $num);
        return $num_parts[0] . '<sub>.' . $num_parts[1] . '</sub>';
    }

    public static function viewFromString($content, $data = [])
    {
        $directoryPath = storage_path('app/content_views');

        // Ensure the directory exists
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath);
        }

        // Cleanup old files
        $files = File::files($directoryPath);
        foreach ($files as $file) {
            if (Carbon::now()->diffInHours(Carbon::createFromTimestamp(filemtime($file))) > 24) {
                File::delete($file);
            }
        }

        // Calculate a hash of the content for the filename
        $hash = md5($content);
        $fileName = "content_views/{$hash}.blade.php";

        // Check if the file exists in the storage path, if not, create it
        $storagePath = storage_path('app/' . $fileName);
        if (!File::exists($storagePath)) {
            file_put_contents($storagePath, $content);
        }

        // Laravel's view loader needs a hint to load from custom paths.
        // We'll add our temporary storage path to its loader.
        view()->addNamespace('content_views', storage_path('app/content_views'));

        return view("content_views::{$hash}", $data);
    }

    public static function getValueFromRequest($routeUrl, $key = '')
    {
        $queryParams = [];
        parse_str(parse_url($routeUrl, PHP_URL_QUERY), $queryParams);

        // Get the value of the key parameter
        if (!empty($queryParams) && array_key_exists($key, $queryParams))
            return $queryParams[$key];
    }
}
