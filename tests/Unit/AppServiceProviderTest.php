<?php

declare(strict_types=1);

use Illuminate\Support\Facades\URL;
use App\Providers\AppServiceProvider;

it('forces https scheme when not local', function (): void {
    $provider = new AppServiceProvider(app());

    // Simulate a non-local environment
    app()->detectEnvironment(fn () => 'production');

    URL::spy();

    $provider->configureUrl();

    URL::shouldHaveReceived('forceScheme')->once()->with('https');
});

it('forces http scheme when local', function (): void {
    $provider = new AppServiceProvider(app());

    // Simulate a local environment
    app()->detectEnvironment(fn () => 'local');

    URL::spy();

    $provider->configureUrl();

    URL::shouldHaveReceived('forceScheme')->once()->with('http');
});

