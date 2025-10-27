<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('branches', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('deployment_id')->constrained('deployments')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_tracking_remote')->default(false);
            $table->string('last_commit')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
}

