<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Git;

use App\Actions\Git\Status;
use App\Services\GitService;

it('throws_runtime_exception_if_deployment_is_not_found', function (): void {
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Deployment not found.');

    // TODO: needs coverage
    $action = new Status(app(GitService::class), null);

    $action->execute();
});
