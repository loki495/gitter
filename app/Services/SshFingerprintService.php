<?php

declare(strict_types=1);

namespace App\Services;

class SshFingerprintService
{
    public function compute(string $path): ?string
    {
        if (! file_exists($path)) {
            return null;
        }

        $cmd = trim(shell_exec('which ssh-keygen'), " \n");
        $cmd = "$cmd -lf {$path} | awk '{print $2}'";
        $output = shell_exec($cmd);

        if (! $output) {
            return null;
        }

        $output = trim($output, " \n");

        return $output ? trim($output) : null;
    }
}

