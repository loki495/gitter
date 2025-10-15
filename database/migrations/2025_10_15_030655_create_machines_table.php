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
            $table->string('ip')->nullable();
            $table->string('ssh_user');
            $table->integer('ssh_port')->default(22);
            $table->string('ssh_key_path')->nullable();
            $table->text('ssh_password_encrypted')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index('ip'); // optional
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
