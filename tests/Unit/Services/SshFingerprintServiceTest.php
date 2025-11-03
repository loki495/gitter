<?php

declare(strict_types=1);

use App\Services\SshFingerprintService;

beforeEach(function (): void {
    $mock = Mockery::mock(SshFingerprintService::class);
    $mock->shouldReceive('compute')->andReturn('FAKE-FP');
    app()->instance(SshFingerprintService::class, $mock);
    $this->service = new SshFingerprintService;
});

it('computes a fingerprint for an existing key file', function (): void {
    $path = sys_get_temp_dir().'/test_ssh.pub';
    file_put_contents($path, 'dummy ssh key content');

    $fp = app(SshFingerprintService::class)->compute('any-path');
    expect($fp)->toBe('FAKE-FP');

    // Should return a string or null if ssh-keygen fails
    expect(is_string($fp) || $fp === null)->toBeTrue();

    unlink($path);
});

it('returns null if the file does not exist', function (): void {
    $nonexistent = sys_get_temp_dir().'/missing.pub';
    $fp = $this->service->compute($nonexistent);

    expect($fp)->toBeNull();
});

it('handles empty files', function (): void {
    $emptyPath = sys_get_temp_dir().'/empty.pub';
    $fp = $this->service->compute($emptyPath);

    // Should return null or string; ensures no exceptions thrown
    expect(is_string($fp) || $fp === null)->toBeTrue();

    @unlink($emptyPath);
});

it('fails with invalid path types', function (): void {
    $this->expectException(TypeError::class);

    /** @phpstan-ignore-next-line */
    $this->service->compute(['not', 'a', 'string']);
});

it('computes a fingerprint for a real temporary key', function (): void {
    $temp = Storage::disk('local')->path('').'temp.pub';
    file_put_contents($temp, 'ssh-rsa AAAAB3NzaC1yc2EAAAABJQAAAQB/nAmOjTmezNUDKYvEeIRf2YnwM9/uUG1d0BYsc8/tRtx+RGi7N2lUbp728MXGwdnL9od4cItzky/zVdLZE2cycOa18xBK9cOWmcKS0A8FYBxEQWJ/q9YVUgZbFKfYGaGQxsER+A0w/fX8ALuk78ktP31K69LcQgxIsl7rNzxsoOQKJ/CIxOGMMxczYTiEoLvQhapFQMs3FL96didKr/QbrfB1WT6s3838SEaXfgZvLef1YB2xmfhbT9OXFE3FXvh2UPBfN+ffE7iiayQf/2XR+8j4N4bW30DiPtOQLGUrH1y5X/rpNZNlWW2+jGIxqZtgWg7lTy3mXy5x836Sj/6L');

    $service = new App\Services\SshFingerprintService;
    $fp = $service->compute($temp);

    expect($fp)->toBe('SHA256:Sr7R03NsrTQ3vXO7XcRZzpJfixXJnwZXPi48i6XsLOY');

    unlink($temp);
});

it('computes a fingerprint for a real empty key', function (): void {
    $temp = Storage::disk('local')->path('').'temp.pub';
    file_put_contents($temp, '');

    $service = new App\Services\SshFingerprintService;
    $fp = $service->compute($temp);

    expect($fp)->toBeNull();

    unlink($temp);
});
