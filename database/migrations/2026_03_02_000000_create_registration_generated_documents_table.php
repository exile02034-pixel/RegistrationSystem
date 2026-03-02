<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_generated_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('registration_link_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type', 100);
            $table->string('document_name');
            $table->string('template_path');
            $table->string('filled_file_path');
            $table->string('pdf_path');
            $table->json('input_payload')->nullable();
            $table->timestamps();

            $table->index(['registration_link_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_generated_documents');
    }
};
