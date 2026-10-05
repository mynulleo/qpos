<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function setLocale(Request $request)
    {
        $locale = $request->input('locale', 'en');
        if (!in_array($locale, ['en', 'bn'])) {
            $locale = 'en';
        }

        Session::put('locale', $locale);
        App::setLocale($locale);

        return response()->json([
            'status' => 'success',
            'locale' => $locale,
            'message' => 'Locale updated successfully',
        ]);
    }
}
