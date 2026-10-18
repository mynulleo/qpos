<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin\System\LibController;
use App\Http\Controllers\Admin\System\TmpController;

// Get Current Environment Route...
Route::get('app/environment', [LibController::class, 'environment']);

// Initialize System Route...
Route::get('app/initialize-systems', [LibController::class, 'systems']);

// Application Authentication Verification Route...
Auth::routes(['verify' => false, 'reset' => false]);

// Application Storage Link Route...
Route::get('/sym', function () {
    File::link(storage_path('app/public'), public_path('storage'));

    return response()->json('Link Create Successfully!');
});

Route::get('/clear-tmp', [TmpController::class, 'tmpClear']);

// Application Cache Clear Route...
Route::get('/clear', function (Request $request) {
    Artisan::call('optimize:clear');

    if ($request->format() == 'html') {
        session()->flash('successMessage', 'Cache Cleared Successfully!');
        return back();
    }

    return response()->json(['message' => 'Cache Cleared Successfully!']);
});
