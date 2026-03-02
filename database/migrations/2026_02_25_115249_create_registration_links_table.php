<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registration_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email');
            $table->string('token')->unique();
            $table->enum('status', ['pending', 'completed', 'incomplete'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_links');
    }
};
