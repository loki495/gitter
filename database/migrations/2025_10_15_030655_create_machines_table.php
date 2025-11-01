<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('type'); // work/staging/production
            $table->string('ip');
            $table->string('ssh_user');
            $table->integer('ssh_port')->default(22);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreignId('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('ssh_key_id')->constrained('ssh_keys')->nullOnDelete();

            $table->index('ip'); // optional
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
