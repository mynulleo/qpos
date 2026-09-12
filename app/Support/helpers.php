<?php

use App\Action\CloudFlare\CloudFlareStoreAction;
use App\Helpers\BreadcrumbHelper;
use App\Helpers\FrontMenuHelper;
use App\Http\Controllers\Base\BaseController;
use App\Models\Website\FrontMenu;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

if (!function_exists('boilerplate')) {
    function boilerplate()
    {
        // Code Here...
    }
}

if (!function_exists('model')) {
    function model($name)
    {
        $model = '\\App\\Models\\' . $name;

        if (!class_exists($model)) {
            throw new \Exception("Model class '$model' does not exist.");
        }

        return new $model();
    }
}

if (!function_exists('artisan')) {
    function artisan($command)
    {
        $exitCode = Artisan::call($command);

        if ($exitCode === 0) {
            echo "Command '$command' executed successfully." . PHP_EOL;
        } else {
            echo "Command '$command' failed with exit code $exitCode." . PHP_EOL;
        }

        return $exitCode;
    }
}

if (!function_exists('vue_to_server_date')) {
    function vue_to_server_date($value)
    {
        if (empty($value) || $value === 'null' || $value === 'undefined') {
            return null;
        }

        $value = trim($value);

        // 1. If already valid Y-m-d format (e.g. 2026-09-11)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        // 2. Check for d-m-Y or d/m/Y explicitly (e.g. 11-09-2026, 01/09/2026)
        if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $value, $m)) {
            $d = (int)$m[1];
            $mo = (int)$m[2];
            $y = (int)$m[3];
            if (checkdate($mo, $d, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $mo, $d);
            }
        }

        // 3. Try parsing with Carbon
        try {
            $cleanValue = str_replace(',', ' ', $value);
            $cleanValue = preg_replace('/\s+/', ' ', $cleanValue);
            $carbon = \Carbon\Carbon::parse($cleanValue);
            if ($carbon) {
                return $carbon->format('Y-m-d');
            }
        } catch (\Throwable $e) {
            // Fallback below
        }

        // 4. Try date_parse as fallback
        $parsed = date_parse($value);
        if (!empty($parsed['year']) && !empty($parsed['month']) && !empty($parsed['day']) && checkdate($parsed['month'], $parsed['day'], $parsed['year'])) {
            return sprintf('%04d-%02d-%02d', $parsed['year'], $parsed['month'], $parsed['day']);
        }

        return null;
    }
}

if (!function_exists('is_base64')) {
    function is_base64($string)
    {
        $string = preg_replace('/data:image\/[a-zA-Z0-9]+;base64,/', '', $string);
        $string = str_replace(' ', '+', $string);
        $decoded = base64_decode($string, true);

        return ($decoded !== false) && (base64_encode($decoded) === $string);
    }
}

if (!function_exists('base64_to_upload_instance')) {
    function base64_to_upload_instance($base64)
    {
        $baseController = new BaseController();
        $rawImage = $baseController->upload($base64, 'admin', null, $base64 = true);

        $remoteUrl = url('/') . '/public/storage/' . $rawImage;
        $fileContents = file_get_contents($remoteUrl);

        $tmpFile = tmpfile();
        fwrite(
            $tmpFile,
            $fileContents
        );

        $tmpFileInfo = stream_get_meta_data($tmpFile);

        $instance = new UploadedFile(
            $tmpFileInfo['uri'],
            basename($remoteUrl),
            mime_content_type($tmpFileInfo['uri']),
            filesize($tmpFileInfo['uri']),
            UPLOAD_ERR_OK,
            true
        );

        return $instance;
    }
}

if (!function_exists('cloudflare')) {
    function cloudflare($file, $folder, $resizeSize, $base64 = false)
    {
        if (empty($file) || $file == 'undefined' || $file == 'null') {
            return;
        }

        if ($base64) {
            $baseController = new BaseController();
            $rawImage = $baseController->upload($file, 'admin', null, $base64 = true);

            $remoteUrl = Storage::url($rawImage);
            $fileContents = file_get_contents($remoteUrl);

            $tmpFile = tmpfile();
            fwrite(
                $tmpFile,
                $fileContents
            );

            $tmpFileInfo = stream_get_meta_data($tmpFile);

            $instance = new UploadedFile(
                $tmpFileInfo['uri'],
                basename($remoteUrl),
                mime_content_type($tmpFileInfo['uri']),
                filesize($tmpFileInfo['uri']),
                UPLOAD_ERR_OK,
                true
            );

            $file = $instance;
        }

        $baseFolder = "upload/{$folder}";

        if (config('app.cloudflare_resize')) {
            $cloudFlareStoreAction = new CloudFlareStoreAction();
            $paths = $cloudFlareStoreAction->execute($file, true, $baseFolder, $resizeSize);

            return $paths;
        }

        return use_intervation($file, $baseFolder, $resizeSize);
    }
}

if (!function_exists('use_intervation')) {
    function use_intervation($file, $baseFolder, $resizeSizes)
    {
        $manager = new ImageManager(new Driver());

        $resultArray = [];
        $image = $manager->read($file);

        $resizeArray = explode(',', $resizeSizes);

        // Original
        $originalFilename = Str::uuid() . '.webp';
        $subFolder = "{$baseFolder}/original";

        if (!Storage::exists($subFolder)) {
            Storage::makeDirectory($subFolder);
        }

        Storage::put(
            "{$subFolder}/{$originalFilename}",
            (string) $image->toWebp(90)
        );

        $resultArray['original'] = "{$subFolder}/{$originalFilename}";

        foreach ($resizeArray as $size) {

            [$width, $height] = explode('x', $size);

            if (!is_numeric($width) || !is_numeric($height)) {
                continue;
            }

            $resized = clone $image;

            $resized->resize($width, $height);

            $filename = Str::uuid() . '.webp';
            $subFolder = "{$baseFolder}/{$size}";

            if (!Storage::exists($subFolder)) {
                Storage::makeDirectory($subFolder);
            }

            Storage::put(
                "{$subFolder}/{$filename}",
                (string) $resized->toWebp(90)
            );

            $resultArray[$size] = "{$subFolder}/{$filename}";
        }

        return $resultArray;
    }
}

if (!function_exists('backend_password_reset_url')) {
    function backend_password_reset_url($email)
    {
        $token = Str::random(64);

        DB::connection('accessdb')->table('password_resets')->where('email', $email)->delete();
        DB::connection('accessdb')->table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => now(),
        ]);

        return url("/password-reset?token={$token}&email=" . urlencode($email));
    }
}

if (!function_exists('uuid')) {
    function uuid()
    {
        return Str::uuid();
    }
}

if (!function_exists('without_cache')) {
    function without_cache($path)
    {
        return asset($path) . cache_busting($path);
    }
}

if (!function_exists('cache_busting')) {
    function cache_busting($path)
    {
        if (file_exists(public_path($path))) {
            $timestamp = filemtime(public_path($path));
            $version = '?id=' . md5($timestamp);

            return $version;
        }

        $alternative = '?id=' . md5(time());

        return $alternative;
    }
}

if (!function_exists('getJsonMediaUrl')) {
    function getJsonMediaUrl($json, $index, $exactIndex = false)
    {
        try {
            // convert from json to array
            if (is_string($json)) {
                $mediaArray = json_decode($json, true);
            } else {
                $mediaArray = $json;
            }

            if ($index == 'original') {
                $path = $mediaArray['original'];
            } else if ($exactIndex === true) {
                $path = $mediaArray[$index];
            } else {
                $mediaPaths = array_values($mediaArray);
                $path = $mediaPaths[$index];
            }

            if (Storage::exists($path)) {
                return Storage::url($path);
            } else {
                return "no_server_image";
            }
        } catch (\Throwable $th) {
            return null;
        }
    }
}
if (!function_exists('emptyImage')) {
    function emptyImage()
    {
        return url('/public/images/noimage.png');
    }
}

if (!function_exists('convertToDatabaseDate')) {
    function convertToDatabaseDate($value)
    {
        if (is_null($value)) {
            return null;
        }
        $date = DateTime::createFromFormat('Y-m-d', $value)
            ?: DateTime::createFromFormat('d/m/Y', $value)
            ?: DateTime::createFromFormat('m/d/Y', $value)
            ?: DateTime::createFromFormat('d-m-Y', $value)
            ?: DateTime::createFromFormat('m-d-Y', $value)
            ?: DateTime::createFromFormat('Y/m/d', $value)
            ?: DateTime::createFromFormat('M j, Y', $value)
            ?: DateTime::createFromFormat('d M, Y', $value);
        if ($date) {
            return $date->format('Y-m-d');
        } else {
            throw new \Exception("Invalid date format: " . $value);
        }
    }
}

if (!function_exists('themeBlade')) {
    function themeBlade(string $path)
    {
        $prefix = 'themes.' . Session::get('theme', 'default');

        return $prefix . '/' . $path;
    }
}

if (!function_exists('themeMenu')) {
    function themeMenu(FrontMenu $frontMenu)
    {
        return FrontMenuHelper::create($frontMenu);
    }
}

if (!function_exists('breadcrumbs')) {
    function breadcrumbs()
    {
        $breadcrumb = new BreadcrumbHelper;
        return $breadcrumb->getAllBreadcrumb();
    }
}

if (!function_exists('getRoute')) {
    function getRoute($item)
    {
        if ($item['type'] == 'internal_link') {
            return route($item['url'], $item['params']);
        }
        if ($item['type'] == 'content') {
            return route('web.content.show', $item['slug']);
        }
        if ($item['type'] == 'outside_website') {
            return $item['url'];
        }
        return '#';
    }
}
