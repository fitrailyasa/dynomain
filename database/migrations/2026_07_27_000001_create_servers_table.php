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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['local', 'ssh'])->default('local');
            $table->string('host')->nullable();
            $table->integer('port')->default(22);
            $table->string('username')->nullable();
            $table->enum('auth_type', ['password', 'key'])->default('password');
            $table->text('password')->nullable();
            $table->text('private_key')->nullable();
            $table->enum('webserver_type', ['nginx', 'apache'])->default('nginx');
            $table->string('config_path')->default('/etc/nginx/sites-available');
            $table->string('symlink_path')->nullable()->default('/etc/nginx/sites-enabled');
            $table->string('reload_command')->default('sudo systemctl reload nginx');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->boolean('is_wildcard')->default(true)->after('ip');
            $table->enum('webserver_type', ['nginx', 'apache'])->default('nginx')->after('is_wildcard');
            $table->enum('target_type', ['proxy', 'webroot'])->default('proxy')->after('webserver_type');
            $table->string('target_destination')->nullable()->after('target_type');
            $table->foreignId('server_id')->nullable()->constrained('servers')->nullOnDelete()->after('target_destination');
            $table->enum('ssl_type', ['none', 'cloudflare', 'certbot', 'custom'])->default('cloudflare')->after('server_id');
            $table->string('ssl_cert_path')->nullable()->after('ssl_type');
            $table->string('ssl_key_path')->nullable()->after('ssl_cert_path');
            $table->text('custom_nginx_config')->nullable()->after('ssl_key_path');
            $table->timestamp('published_at')->nullable()->after('custom_nginx_config');
            $table->enum('publish_status', ['pending', 'published', 'failed'])->default('pending')->after('published_at');
            $table->text('publish_log')->nullable()->after('publish_status');
        });

        Schema::table('subdomains', function (Blueprint $table) {
            $table->enum('webserver_type', ['nginx', 'apache'])->default('nginx')->after('ip');
            $table->enum('target_type', ['proxy', 'webroot'])->default('proxy')->after('webserver_type');
            $table->string('target_destination')->nullable()->after('target_type');
            $table->foreignId('server_id')->nullable()->constrained('servers')->nullOnDelete()->after('target_destination');
            $table->enum('ssl_type', ['none', 'cloudflare', 'certbot', 'custom'])->default('cloudflare')->after('server_id');
            $table->string('ssl_cert_path')->nullable()->after('ssl_type');
            $table->string('ssl_key_path')->nullable()->after('ssl_cert_path');
            $table->text('custom_nginx_config')->nullable()->after('ssl_key_path');
            $table->timestamp('published_at')->nullable()->after('custom_nginx_config');
            $table->enum('publish_status', ['pending', 'published', 'failed'])->default('pending')->after('published_at');
            $table->text('publish_log')->nullable()->after('publish_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subdomains', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
            $table->dropColumn([
                'webserver_type', 'target_type', 'target_destination', 'server_id',
                'ssl_type', 'ssl_cert_path', 'ssl_key_path', 'custom_nginx_config',
                'published_at', 'publish_status', 'publish_log'
            ]);
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
            $table->dropColumn([
                'is_wildcard', 'webserver_type', 'target_type', 'target_destination', 'server_id',
                'ssl_type', 'ssl_cert_path', 'ssl_key_path', 'custom_nginx_config',
                'published_at', 'publish_status', 'publish_log'
            ]);
        });

        Schema::dropIfExists('servers');
    }
};
