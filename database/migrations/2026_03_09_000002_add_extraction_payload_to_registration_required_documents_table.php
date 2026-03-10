<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_required_documents', function (Blueprint $table) {
            $table->json('extraction_payload')->nullable()->after('mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('registration_required_documents', function (Blueprint $table) {
            $table->dropColumn('extraction_payload');
        });
    }
};

