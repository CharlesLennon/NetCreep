<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', fn() => view('base'));

Route::get('/devices', fn() => view('devices'))->name('devices');

Route::get('/device/{mac}/description', function($mac) {
    return view('monaco-editor', [
        'id' => $mac,
        'class' => \App\Models\Device::class,
        'field' => "description",
        "language" => "blade"
    ]);
})->name('monaco-editor-device-description');

Route::get('/settings', fn() => view('settings'))->name('settings');
