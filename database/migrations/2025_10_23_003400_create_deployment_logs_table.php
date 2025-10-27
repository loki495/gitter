<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateDeploymentLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deployment_logs', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('deployment_id')->constrained('deployments')->cascadeOnDelete();
            $table->string('action');
            $table->text('command');
            $table->longText('output');
            $table->integer('exit_code')->nullable();
            $table->timestamp('executed_at');
            $table->integer('duration_ms')->nullable();
            $table->timestamps();
            $table->index(['deployment_id', 'executed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployment_logs');
    }
}
