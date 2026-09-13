<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn([
                'webserver_type',
                'config_path',
                'symlink_path',
                'reload_command',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->enum('webserver_type', ['nginx', 'apache'])->default('nginx');
            $table->string('config_path')->default('/etc/nginx/sites-available');
            $table->string('symlink_path')->nullable()->default('/etc/nginx/sites-enabled');
            $table->string('reload_command')->default('sudo systemctl reload nginx');
        });
    }
};
