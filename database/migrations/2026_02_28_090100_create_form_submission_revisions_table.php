<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submission_revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_submission_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('revision_number');
            $table->string('event', 100)->default('submitted');
            $table->string('actor_type', 50)->default('client');
            $table->string('actor_identifier')->nullable();
            $table->json('snapshot');
            $table->timestamps();

            $table->unique(['form_submission_id', 'revision_number']);
            $table->index(['form_submission_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submission_revisions');
    }
};
