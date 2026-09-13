<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->recreateDomainsTable();
            $this->recreateSubdomainsTable();
        } else {
            Schema::table('domains', function (Blueprint $table) {
                $table->enum('target_type', ['proxy', 'webroot', 'laravel', 'wordpress'])->default('proxy')->change();
            });
            Schema::table('subdomains', function (Blueprint $table) {
                $table->enum('target_type', ['proxy', 'webroot', 'laravel', 'wordpress'])->default('proxy')->change();
            });
        }
    }

    protected function recreateDomainsTable(): void
    {
        DB::statement('CREATE TABLE domains_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            ip TEXT NOT NULL,
            is_wildcard INTEGER NOT NULL DEFAULT 1,
            webserver_type TEXT NOT NULL DEFAULT \'nginx\',
            target_type TEXT NOT NULL DEFAULT \'proxy\',
            target_destination TEXT,
            server_id INTEGER,
            ssl_type TEXT NOT NULL DEFAULT \'cloudflare\',
            ssl_cert_path TEXT,
            ssl_key_path TEXT,
            custom_nginx_config TEXT,
            custom_config_mode TEXT DEFAULT \'default\',
            published_at TIMESTAMP,
            publish_status TEXT NOT NULL DEFAULT \'pending\',
            publish_log TEXT,
            status INTEGER NOT NULL DEFAULT 1,
            created_at TIMESTAMP,
            updated_at TIMESTAMP,
            deleted_at TIMESTAMP
        )');

        DB::statement('INSERT INTO domains_new (id, name, ip, is_wildcard, webserver_type, target_type, target_destination, server_id, ssl_type, ssl_cert_path, ssl_key_path, custom_nginx_config, custom_config_mode, published_at, publish_status, publish_log, status, created_at, updated_at, deleted_at) SELECT id, name, ip, is_wildcard, webserver_type, target_type, target_destination, server_id, ssl_type, ssl_cert_path, ssl_key_path, custom_nginx_config, custom_config_mode, published_at, publish_status, publish_log, status, created_at, updated_at, deleted_at FROM domains');

        DB::statement('DROP TABLE domains');
        DB::statement('ALTER TABLE domains_new RENAME TO domains');
    }

    protected function recreateSubdomainsTable(): void
    {
        DB::statement('CREATE TABLE subdomains_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            ip TEXT NOT NULL,
            webserver_type TEXT NOT NULL DEFAULT \'nginx\',
            target_type TEXT NOT NULL DEFAULT \'proxy\',
            target_destination TEXT,
            server_id INTEGER,
            domain_id INTEGER,
            ssl_type TEXT NOT NULL DEFAULT \'cloudflare\',
            ssl_cert_path TEXT,
            ssl_key_path TEXT,
            custom_nginx_config TEXT,
            custom_config_mode TEXT DEFAULT \'default\',
            published_at TIMESTAMP,
            publish_status TEXT NOT NULL DEFAULT \'pending\',
            publish_log TEXT,
            status INTEGER NOT NULL DEFAULT 1,
            created_at TIMESTAMP,
            updated_at TIMESTAMP,
            deleted_at TIMESTAMP
        )');

        DB::statement('INSERT INTO subdomains_new (id, name, ip, webserver_type, target_type, target_destination, server_id, domain_id, ssl_type, ssl_cert_path, ssl_key_path, custom_nginx_config, custom_config_mode, published_at, publish_status, publish_log, status, created_at, updated_at, deleted_at) SELECT id, name, ip, webserver_type, target_type, target_destination, server_id, domain_id, ssl_type, ssl_cert_path, ssl_key_path, custom_nginx_config, custom_config_mode, published_at, publish_status, publish_log, status, created_at, updated_at, deleted_at FROM subdomains');

        DB::statement('DROP TABLE subdomains');
        DB::statement('ALTER TABLE subdomains_new RENAME TO subdomains');
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // Same logic - just recreates without laravel option
            $this->recreateDomainsTable();
            $this->recreateSubdomainsTable();
        } else {
            Schema::table('domains', function (Blueprint $table) {
                $table->enum('target_type', ['proxy', 'webroot'])->default('proxy')->change();
            });
            Schema::table('subdomains', function (Blueprint $table) {
                $table->enum('target_type', ['proxy', 'webroot'])->default('proxy')->change();
            });
        }
    }
};
