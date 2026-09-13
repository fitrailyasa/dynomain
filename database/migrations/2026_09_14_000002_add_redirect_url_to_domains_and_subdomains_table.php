<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->string('redirect_url')->nullable()->after('target_destination');
        });

        Schema::table('subdomains', function (Blueprint $table) {
            $table->string('redirect_url')->nullable()->after('target_destination');
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn('redirect_url');
        });

        Schema::table('subdomains', function (Blueprint $table) {
            $table->dropColumn('redirect_url');
        });
    }
};
