<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Domain;
use App\Models\Server;
use App\Services\WebserverGeneratorService;
use App\Services\WebserverPublisherService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WebserverPublishTest extends TestCase
{
    use RefreshDatabase;

    public function test_generator_creates_nginx_wildcard_config_with_cloudflare_ips()
    {
        $domain = Domain::create([
            'name' => 'mydomain.com',
            'ip' => '127.0.0.1',
            'status' => true,
            'is_wildcard' => true,
            'webserver_type' => 'nginx',
            'target_type' => 'proxy',
            'target_destination' => 'http://127.0.0.1:8000',
            'ssl_type' => 'cloudflare',
        ]);

        $generator = new WebserverGeneratorService();
        $config = $generator->generateNginxConfig($domain);

        $this->assertStringContainsString('server_name mydomain.com *.mydomain.com;', $config);
        $this->assertStringContainsString('set_real_ip_from 103.21.244.0/22;', $config);
        $this->assertStringContainsString('real_ip_header CF-Connecting-IP;', $config);
        $this->assertStringContainsString('proxy_pass http://127.0.0.1:8000;', $config);
    }

    public function test_generator_creates_apache_wildcard_config()
    {
        $domain = Domain::create([
            'name' => 'mydomain.com',
            'ip' => '127.0.0.1',
            'status' => true,
            'is_wildcard' => true,
            'webserver_type' => 'apache',
            'target_type' => 'proxy',
            'target_destination' => 'http://127.0.0.1:8000',
        ]);

        $generator = new WebserverGeneratorService();
        $config = $generator->generateApacheConfig($domain);

        $this->assertStringContainsString('ServerName mydomain.com', $config);
        $this->assertStringContainsString('ServerAlias *.mydomain.com', $config);
        $this->assertStringContainsString('RemoteIPHeader CF-Connecting-IP', $config);
    }

    public function test_local_publisher_saves_config_to_storage()
    {
        $domain = Domain::create([
            'name' => 'testpub.com',
            'ip' => '127.0.0.1',
            'status' => true,
            'is_wildcard' => true,
            'webserver_type' => 'nginx',
        ]);

        $server = Server::create([
            'name' => 'Test Local Server',
            'type' => 'local',
            'webserver_type' => 'nginx',
            'config_path' => storage_path('app/webservers'),
            'symlink_path' => storage_path('app/webservers/enabled'),
            'reload_command' => 'echo "Reload test"',
            'status' => true,
        ]);

        $generator = new WebserverGeneratorService();
        $publisher = new WebserverPublisherService($generator);

        $result = $publisher->publish($domain, $server);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('domains', [
            'id' => $domain->id,
            'publish_status' => 'published',
        ]);
    }
}
