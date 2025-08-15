<?php

use App\Models\PracticeSet;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('dashboard', 'admin.dashboard')->name('dashboard');
    Volt::route('sites', 'admin.sites.index')->name('sites.index');
});

require __DIR__.'/auth.php';
