<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->index();
            $table->string('service_name');
            $table->text('encrypted_token');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'service_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credentials');
    }
};
