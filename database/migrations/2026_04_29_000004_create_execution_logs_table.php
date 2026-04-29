<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execution_id')->constrained('workflow_executions')->cascadeOnDelete();
            $table->string('node_id');
            $table->enum('status', ['running', 'success', 'failed']);
            $table->json('input_data')->nullable();
            $table->json('output_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['execution_id', 'node_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('execution_logs');
    }
};
