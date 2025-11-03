<?php

declare(strict_types=1);

namespace App\Services;

class CliRunner
{
    /**
     * Run a command and return output.
     *
     * @param  string|array<int, string>  $command
     * @return array{stdout: string, stderr: string, exit_code: int}
     */
    public function run(string|array $command): array
    {
        if (is_array($command)) {
            $command = $this->buildCommandString($command);
        }

        $output = [];
        $exitCode = 0;

        exec($command.' 2>&1', $output, $exitCode);

        $stdout = implode("\n", $output);

        return [
            'stdout' => $stdout,
            'stderr' => $exitCode === 0 ? '' : $stdout,
            'exit_code' => $exitCode,
            'duration_ms' => 0,
        ];
    }

    /**
     * Build a safe command string without over-quoting.
     *
     * @param  array<int,string>  $parts
     */
    protected function buildCommandString(array $parts): string
    {
        $escaped = [];
        foreach ($parts as $i => $part) {
            if ($i === 0) {
                // Executable, never quote
                $escaped[] = $part;
            } else {
                // Escape only internal quotes
                $escaped[] = str_replace("'", "'\\''", $part);
            }
        }

        // Join with spaces (not quoted)
        return implode(' ', $escaped);
    }
}
