<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_required_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('registration_link_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 100);
            $table->string('document_name');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('mime_type', 150)->nullable();
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['registration_link_id', 'document_type'], 'registration_required_docs_unique');
            $table->index(['registration_link_id', 'created_at'], 'registration_required_docs_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_required_documents');
    }
};

