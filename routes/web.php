<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('/', 'admin.dashboard')->name('dashboard');

    Volt::route('machines', 'admin.machines.index')->name('machines.index');
    Volt::route('machines/create', 'admin.machines.edit')->name('machines.create');
    Volt::route('machines/{machine}', 'admin.machines.edit')->name('machines.edit');

    Volt::route('sites', 'admin.sites.index')->name('sites.index');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
