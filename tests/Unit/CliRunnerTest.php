<?php

declare(strict_types=1);

use App\Services\CliRunner;

it('runs a simple string command and returns expected output', function (): void {
    $runner = new CliRunner();

    // `echo` returns text to stdout, exit_code = 0
    $result = $runner->run('echo "hello"');

    expect($result['stdout'])->toContain('hello')
        ->and($result['stderr'])->toBe('')
        ->and($result['exit_code'])->toBe(0);
});

it('escapes and joins array commands correctly', function (): void {
    $runner = new CliRunner();

    // Array command — should be joined into a single string
    $result = $runner->run(['echo', 'test with spaces']);

    expect($result['stdout'])->toContain('test with spaces')
        ->and($result['stderr'])->toBe('')
        ->and($result['exit_code'])->toBe(0);
});

it('captures stderr when command fails', function (): void {
    $runner = new CliRunner();

    // Nonexistent command => nonzero exit code
    $result = $runner->run('bash -c "nonexistent_command"');

    expect($result['exit_code'])->not->toBe(0)
        ->and($result['stderr'])->not->toBe('');
});
