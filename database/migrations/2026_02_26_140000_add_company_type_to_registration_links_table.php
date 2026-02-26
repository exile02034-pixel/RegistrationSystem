<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_links', function (Blueprint $table) {
            $table->string('company_type')->default('corp')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('registration_links', function (Blueprint $table) {
            $table->dropColumn('company_type');
        });
    }
};
