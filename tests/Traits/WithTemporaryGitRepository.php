<?php

declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

trait WithTemporaryGitRepository
{
    protected string $repoPath;

    public function setupGitRepository(): void
    {
        $this->repoPath = storage_path('framework/testing/git-repo-'.bin2hex(random_bytes(5)));

        File::makeDirectory($this->repoPath, 0755, true, true);

        $this->runInRepo('git init -b main');
        $this->runInRepo('git config user.email "testing@example.com"');
        $this->runInRepo('git config user.name "Testing"');
        $this->runInRepo('touch README.md');
        $this->runInRepo('git add README.md');
        $this->runInRepo('git commit -m "Initial commit"');
        $this->runInRepo('git branch feature-branch');
    }

    public function cleanupGitRepository(): void
    {
        File::deleteDirectory($this->repoPath);
    }

    protected function runInRepo(string $command): string
    {
        $process = Process::fromShellCommandline($command, $this->repoPath);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'Git command failed: %s%sError: %s',
                $command,
                PHP_EOL,
                $process->getErrorOutput()
            ));
        }

        return $process->getOutput();
    }
}
