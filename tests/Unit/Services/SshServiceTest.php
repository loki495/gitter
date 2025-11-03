<?php

declare(strict_types=1);

use App\Models\Machine;
use App\Models\SshKey;
use App\Models\User;
use App\Services\CliRunner;
use App\Services\SshService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('builds ssh command and calls runner', function (): void {
    $sshKey = SshKey::factory()->create([
        'user_id' => $this->user->id,
        'filename' => 'deploy_key',
    ]);

    $machine = Machine::factory()->make([
        'ssh_user' => 'andres',
        'ip' => '192.168.1.145',
        'ssh_key_id' => $sshKey->id,
        'ssh_port' => 22222,
        'user_id' => $this->user->id
    ]);

    $ssh = app(SshService::class);
    $result = $ssh->run($machine, 'uptime');

    expect($result['stdout'])->not()->toBe('')
        ->and($result['stderr'])->toBe('')
        ->and($result['exit_code'])->toBe(0);
});
