<?php

namespace App\Services;

class CliRunner
{
    /**
     * Run a command and return output.
     *
     * @param string|array<int, string> $command
     * @return array{stdout: string, stderr: string, exit_code: int}
     */
    public function run(string|array $command): array
    {
        if (is_array($command)) {
            $command = implode(' ', array_map('escapeshellarg', $command));
        }

        $output = [];
        $exitCode = 0;

        exec($command . ' 2>&1', $output, $exitCode);

        return [
            'stdout' => implode("\n", $output),
            'stderr' => $exitCode === 0 ? '' : implode("\n", $output),
            'exit_code' => $exitCode,
        ];
    }
}

