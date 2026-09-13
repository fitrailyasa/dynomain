<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->enum('custom_config_mode', ['default', 'replace', 'add'])->default('default')->after('custom_nginx_config');
        });

        Schema::table('subdomains', function (Blueprint $table) {
            $table->enum('custom_config_mode', ['default', 'replace', 'add'])->default('default')->after('custom_nginx_config');
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn('custom_config_mode');
        });

        Schema::table('subdomains', function (Blueprint $table) {
            $table->dropColumn('custom_config_mode');
        });
    }
};
