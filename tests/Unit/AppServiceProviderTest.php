<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;

it('forces https scheme when not local', function (): void {
    $provider = new AppServiceProvider(app());

    // Simulate a non-local environment
    app()->detectEnvironment(fn (): string => 'production');

    URL::spy();

    $provider->configureUrl();

    URL::shouldHaveReceived('forceScheme')->once()->with('https');
});

it('forces http scheme when local', function (): void {
    $provider = new AppServiceProvider(app());

    // Simulate a local environment
    app()->detectEnvironment(fn (): string => 'local');

    URL::spy();

    $provider->configureUrl();

    URL::shouldHaveReceived('forceScheme')->once()->with('http');
});
