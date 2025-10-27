<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('/', 'admin.dashboard')->name('dashboard');

    Volt::route('machines', 'admin.machines.index')->name('machines.index');
    Volt::route('machines/create', 'admin.machines.edit')->name('machines.create');
    Volt::route('machines/{machine}', 'admin.machines.edit')->name('machines.edit');
    Volt::route('machines/{machine}/deployments', 'admin.machines.deployments')->name('machines.deployments');

    Volt::route('websites', 'admin.websites.index')->name('websites.index');
    Volt::route('websites/create', 'admin.websites.edit')->name('websites.create');
    Volt::route('websites/{website}', 'admin.websites.edit')->name('websites.edit');

    Volt::route('websites/{website}/deployments', 'admin.deployments.index')->name('deployments.index');
    Volt::route('websites/{website}/deployments/create', 'admin.deployments.edit')->name('deployments.create');
    Volt::route('websites/{website}/deployments/{deployment}', 'admin.deployments.edit')->name('deployments.edit');

    Volt::route('website/{website}/deployments/{deployment}/branches', 'admin.branches.index')->name('branches.index');
    Volt::route('website/{website}/deployments/{deployment}/branches/create', 'admin.branches.edit')->name('branches.create');
    Volt::route('website/{website}/deployments/{deployment}/branches/{branch}', 'admin.branches.edit')->name('branches.edit');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
