<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_company_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('company_type', ['corp', 'sole_prop', 'opc']);
            $table->timestamps();
            $table->unique(['user_id', 'company_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_company_types');
    }
};
