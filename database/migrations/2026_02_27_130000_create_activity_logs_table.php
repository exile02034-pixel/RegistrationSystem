<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('description');
            $table->foreignUuid('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('performed_by_email')->nullable();
            $table->string('performed_by_name')->nullable();
            $table->string('performed_by_role')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index('performed_by_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
