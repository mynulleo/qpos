<?php

namespace App\Helpers;

use App\Models\Page;
use App\Models\PageSeo;
use App\Models\PageSection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;

class GlobalHelper extends Model
{
    public static function get_guard()
    {
        if (Auth::guard('admin')->check()) {
            return 'admin';
        } elseif (Auth::guard('web')->check()) {
            return 'user';
        } else {
            return null;
        }
    }
    public static function get_page_section($page, $section_title, $field, $image = false, $size = 'original')
    {
        $page = Page::where('title', $page)->first();

        if ($image) {
            $section = PageSection::where('page_title', $section_title)->first()?->image;
            $image = $section[$size] ?? null;
            return $image;
        }

        if ($page != null) {
            return PageSection::where('page_title', $section_title)->first()?->{$field} ?? '';
        }

        return "";
    }

    public static function getPageSection($pageid, $slug, $image = false, $size = 'original')
    {
        $data = PageSection::where('page_id', $pageid)->where('slug', $slug)->first();
        if ($image) {
            $img = $data->image;
            $sectionImage = $img[$size] ?? null;
            return $sectionImage;
        }
        return $data;
    }

    public static function get_page_seo($page_title)
    {
        $page = Page::where('title', $page_title)->first();
        return PageSeo::with('page')->where('page_id', $page->id ?? '')->first();
    }
    public static function getAllRouteNames()
    {
        $routes = Route::getRoutes();
        $routeNames = [];
        foreach ($routes as $route) {
            /* if (!in_array('auth:admin', $route->gatherMiddleware()) &&
            !str_starts_with($route->getName(), 'admin.') &&
            $route->getName()
            ) {
            $routeNames[] = $route->getName();
            } */
            $name = $route->getName();
            if ($name && !in_array('auth:admin', $route->gatherMiddleware()) && !str_starts_with($name, 'admin.')) {
                $humanReadableName = str_replace('.', ' ', ucfirst($name));
                $routeNames[] = [
                    'key' => $name,
                    'name' => $humanReadableName,
                ];
            }
        }
        return $routeNames;
    }

    /**
     * Convert English digits in a number/string to Bangla digits
     *
     * @param  string|int|float|null  $input
     * @return string
     */
    public static function toBanglaDigits($input): string
    {
        $map = [
            '0' => '০',
            '1' => '১',
            '2' => '২',
            '3' => '৩',
            '4' => '৪',
            '5' => '৫',
            '6' => '৬',
            '7' => '৭',
            '8' => '৮',
            '9' => '৯',
        ];

        $string = (string) $input;

        return strtr($string, $map);
    }

    function numberToBanglaWords($number)
    {
        $ones = [
            0 => '',
            1 => 'এক',
            2 => 'দুই',
            3 => 'তিন',
            4 => 'চার',
            5 => 'পাঁচ',
            6 => 'ছয়',
            7 => 'সাত',
            8 => 'আট',
            9 => 'নয়',
            10 => 'দশ',
            11 => 'এগারো',
            12 => 'বারো',
            13 => 'তেরো',
            14 => 'চৌদ্দ',
            15 => 'পনেরো',
            16 => 'ষোল',
            17 => 'সতেরো',
            18 => 'আঠারো',
            19 => 'উনিশ'
        ];

        $tens = [
            2 => 'বিশ',
            3 => 'ত্রিশ',
            4 => 'চল্লিশ',
            5 => 'পঞ্চাশ',
            6 => 'ষাট',
            7 => 'সত্তর',
            8 => 'আশি',
            9 => 'নব্বই'
        ];

        $units = [
            10000000 => 'কোটি',
            100000 => 'লক্ষ',
            1000 => 'হাজার',
            100 => 'শত'
        ];

        // =========================
        // Convert number to words
        // =========================
        $convert = function ($n) use (&$convert, $ones, $tens, $units) {

            if ($n < 20) {
                return $ones[$n];
            }

            if ($n < 100) {
                return $tens[intval($n / 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');
            }

            foreach ($units as $value => $label) {
                if ($n >= $value) {
                    $count = intval($n / $value);
                    $remainder = $n % $value;

                    return $convert($count) . ' ' . $label .
                        ($remainder ? ' ' . $convert($remainder) : '');
                }
            }
        };

        // =========================
        // Split taka & paisa
        // =========================
        $number = number_format($number, 2, '.', '');
        list($taka, $paisa) = explode('.', $number);

        $taka = intval($taka);
        $paisa = intval($paisa);

        // =========================
        // Final string
        // =========================
        $takaWords = $taka > 0 ? $convert($taka) . ' টাকা' : '';
        $paisaWords = $paisa > 0 ? ' ' . $convert($paisa) . ' পয়সা' : '';

        return trim($takaWords . $paisaWords) . '.';
    }
}
