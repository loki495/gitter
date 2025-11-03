<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ssh_keys', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained('users');
            $table->string('name');
            $table->string('filename')->unique();
            $table->enum('type', ['private', 'public'])->default('private');
            $table->foreignId('machine_id')->nullable()->constrained()->nullOnDelete();
            $table->string('fingerprint')->nullable();
            $table->timestamps();
        });

        // Ensure directory exists with correct perms at migration time if running locally
        // (This is only best-effort — deployment should ensure the folder exists and is owned by web user)
        if (! file_exists(storage_path('ssh'))) {
            mkdir(storage_path('ssh'), 0700, true);
            chmod(storage_path('ssh'), 0700);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ssh_keys');
    }
};
