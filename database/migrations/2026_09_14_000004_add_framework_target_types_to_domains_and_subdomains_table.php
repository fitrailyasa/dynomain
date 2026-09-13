<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            Schema::table('domains', function (Blueprint $table) {
                $table->enum('target_type', [
                    'proxy', 'webroot', 'laravel', 'wordpress', 'redirect',
                    'codeigniter', 'react', 'next', 'vue', 'nuxt'
                ])->default('proxy')->change();
            });
            Schema::table('subdomains', function (Blueprint $table) {
                $table->enum('target_type', [
                    'proxy', 'webroot', 'laravel', 'wordpress', 'redirect',
                    'codeigniter', 'react', 'next', 'vue', 'nuxt'
                ])->default('proxy')->change();
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            Schema::table('domains', function (Blueprint $table) {
                $table->enum('target_type', [
                    'proxy', 'webroot', 'laravel', 'wordpress', 'redirect'
                ])->default('proxy')->change();
            });
            Schema::table('subdomains', function (Blueprint $table) {
                $table->enum('target_type', [
                    'proxy', 'webroot', 'laravel', 'wordpress', 'redirect'
                ])->default('proxy')->change();
            });
        }
    }
};
