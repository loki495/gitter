<?php

declare(strict_types=1);

use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\UpdateBranch;
use App\Actions\Branch\DeleteBranch;
use App\Actions\Branch\PullDeploymentBranches;
use App\Models\User;
use App\Models\Branch;
use App\Models\Deployment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('creates a branch successfully', function (): void {
    $deployment = Deployment::factory()->create(['user_id' => $this->user->id]);

    $action = new CreateBranch();
    $branch = $action->execute($deployment, [
        'name' => 'main',
        'commit_hash' => 'abc123',
        'user_id' => $this->user->id,
    ]);

    expect($branch)->toBeInstanceOf(Branch::class)
        ->and($branch->deployment_id)->toBe($deployment->id)
        ->and($branch->name)->toBe('main');
});

it('fails to create branch with invalid data', function (): void {
    $deployment = Deployment::factory()->create(['user_id' => $this->user->id]);
    $action = new CreateBranch();
    expect(fn (): \App\Models\Branch => $action->execute($deployment, ['name' => null]))->toThrow("The name field is required.");
});

it('updates a branch successfully', function (): void {
    $deployment = Deployment::factory()->create(['user_id' => $this->user->id]);
    $branch = Branch::factory()->create(['deployment_id' => $deployment->id]);
    $action = new UpdateBranch();

    $updated = $action->execute($branch, ['name' => 'develop']);

    expect($updated->name)->toBe('develop')
        ->and($updated->id)->toBe($branch->id);
});

it('fails to update branch if missing required fields', function (): void {
    $deployment = Deployment::factory()->create(['user_id' => $this->user->id]);
    $branch = Branch::factory()->create(['deployment_id' => $deployment->id]);
    $action = new UpdateBranch();

    expect(fn (): \App\Models\Branch => $action->execute($branch, ['name' => null]))->toThrow("The name field is required.");
});

it('deletes a branch successfully', function (): void {
    $deployment = Deployment::factory()->create(['user_id' => $this->user->id]);
    $branch = Branch::factory()->create(['deployment_id' => $deployment->id]);
    $action = new DeleteBranch();

    $action->execute($branch);

    expect(Branch::find($branch->id))->toBeNull();
});

it('handles delete failure gracefully', function (): void {
    $deployment = Deployment::factory()->create(['user_id' => $this->user->id]);
    $branch = Branch::factory()->create(['deployment_id' => $deployment->id]);

    $action = new DeleteBranch();

    // Delete the parent deployment to break FK
    $deployment->delete();

    expect(fn (): bool => $action->execute($branch))
        ->toThrow(\Exception::class);
});
