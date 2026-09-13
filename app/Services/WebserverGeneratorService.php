<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Subdomain;

class WebserverGeneratorService
{
    /**
     * Generate config string based on model and webserver type.
     */
    public function generate(Domain|Subdomain $item, ?string $webserverType = null): string
    {
        $type = $webserverType ?? $item->webserver_type ?? 'nginx';

        if ($type === 'apache') {
            return $this->generateApacheConfig($item);
        }

        return $this->generateNginxConfig($item);
    }

    /**
     * Get filename for configuration file.
     */
    public function getFilename(Domain|Subdomain $item): string
    {
        $domainName = $item instanceof Domain ? $item->name : $item->name . '.' . ($item->domain ? $item->domain->name : 'local');
        $ext = ($item->webserver_type === 'apache') ? '.conf' : '.conf';
        return strtolower(preg_replace('/[^a-zA-Z0-9\._-]/', '_', $domainName)) . $ext;
    }

    /**
     * Generate Nginx Virtual Host Config.
     */
    public function generateNginxConfig(Domain|Subdomain $item): string
    {
        $isWildcard = ($item instanceof Domain) ? (bool) $item->is_wildcard : false;
        $domainName = $item instanceof Domain ? $item->name : $item->name . '.' . ($item->domain ? $item->domain->name : '');
        
        $serverNames = $domainName;
        if ($isWildcard && !str_starts_with($domainName, '*.')) {
            $serverNames = "{$domainName} *.{$domainName}";
        }

        $targetType = $item->target_type ?? 'proxy';
        $targetDest = $item->target_destination ?? ($item->ip ? "http://{$item->ip}" : 'http://127.0.0.1:8000');
        if ($targetType === 'proxy' && !preg_match('/^https?:\/\//i', $targetDest)) {
            $targetDest = "http://{$targetDest}";
        }

        $sslType = $item->ssl_type ?? 'cloudflare';
        $sslCertPath = $item->ssl_cert_path ?? '/etc/ssl/certs/ssl-cert-snakeoil.pem';
        $sslKeyPath = $item->ssl_key_path ?? '/etc/ssl/private/ssl-cert-snakeoil.key';
        $customConfig = trim($item->custom_nginx_config ?? '');
        $customConfigMode = $item->custom_config_mode ?? 'default';

        if ($customConfigMode === 'replace' && !empty($customConfig)) {
            return $this->generateNginxReplaceConfig($item, $customConfig, $domainName, $serverNames);
        }

        $cloudflareIps = [
            '103.21.244.0/22',
            '103.31.4.0/22',
            '141.101.64.0/18',
            '108.162.192.0/18',
            '190.93.240.0/20',
            '188.114.96.0/20',
            '197.234.240.0/22',
            '198.41.128.0/17',
            '162.158.0.0/15',
            '104.16.0.0/13',
            '104.24.0.0/14',
            '172.64.0.0/13',
            '131.0.72.0/22',
        ];

        $cfRealIpBlock = "    # Cloudflare Real IP Header Configuration\n";
        foreach ($cloudflareIps as $ipRange) {
            $cfRealIpBlock .= "    set_real_ip_from {$ipRange};\n";
        }
        $cfRealIpBlock .= "    real_ip_header CF-Connecting-IP;\n";

        $output = "# Auto-generated Nginx Config by Dynomain\n";
        $output .= "# Domain: {$domainName}\n";
        $output .= "# Generated At: " . date('Y-m-d H:i:s') . "\n\n";

        // HTTP Block
        $output .= "server {\n";
        $output .= "    listen 80;\n";
        $output .= "    listen [::]:80;\n";
        $output .= "    server_name {$serverNames};\n\n";
        $output .= $cfRealIpBlock . "\n";

        if ($sslType === 'certbot' || $sslType === 'custom') {
            $output .= "    # Redirect HTTP to HTTPS\n";
            $output .= "    return 301 https://\$host\$request_uri;\n";
            $output .= "}\n\n";

            // HTTPS Block
            $output .= "server {\n";
            $output .= "    listen 443 ssl http2;\n";
            $output .= "    listen [::]:443 ssl http2;\n";
            $output .= "    server_name {$serverNames};\n\n";
            $output .= "    ssl_certificate {$sslCertPath};\n";
            $output .= "    ssl_certificate_key {$sslKeyPath};\n\n";
            $output .= $cfRealIpBlock . "\n";
        }

        if ($targetType === 'webroot') {
            $output .= "    root {$targetDest};\n";
            $output .= "    index index.php index.html index.htm;\n\n";
            $output .= "    location / {\n";
            $output .= "        try_files \$uri \$uri/ /index.php?\$query_string;\n";
            $output .= "    }\n\n";
            $output .= "    location ~ \\.php$ {\n";
            $output .= "        include snippets/fastcgi-php.conf;\n";
            $output .= "        fastcgi_pass unix:/run/php/php8.3-fpm.sock;\n";
            $output .= "    }\n";
        } else {
            $output .= "    location / {\n";
            $output .= "        proxy_pass {$targetDest};\n";
            $output .= "        proxy_set_header Host \$host;\n";
            $output .= "        proxy_set_header X-Real-IP \$remote_addr;\n";
            $output .= "        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;\n";
            $output .= "        proxy_set_header X-Forwarded-Proto \$scheme;\n";
            $output .= "        proxy_http_version 1.1;\n";
            $output .= "        proxy_set_header Upgrade \$http_upgrade;\n";
            $output .= "        proxy_set_header Connection \"upgrade\";\n";
            $output .= "    }\n";
        }

        if (!empty($customConfig) && $customConfigMode === 'add') {
            $output .= "\n    # Custom Directives (Add Mode)\n";
            $output .= "    " . str_replace("\n", "\n    ", $customConfig) . "\n";
        }

        $output .= "}\n";

        return $output;
    }

    /**
     * Generate Nginx config with REPLACE mode (custom directives only).
     */
    protected function generateNginxReplaceConfig(Domain|Subdomain $item, string $customConfig, string $domainName, string $serverNames): string
    {
        $output = "# Custom Nginx Config (Replace Mode) by Dynomain\n";
        $output .= "# Domain: {$domainName}\n";
        $output .= "# Generated At: " . date('Y-m-d H:i:s') . "\n\n";

        $output .= $customConfig . "\n";

        return $output;
    }

    /**
     * Generate Apache Virtual Host Config.
     */
    public function generateApacheConfig(Domain|Subdomain $item): string
    {
        $isWildcard = ($item instanceof Domain) ? (bool) $item->is_wildcard : false;
        $domainName = $item instanceof Domain ? $item->name : $item->name . '.' . ($item->domain ? $item->domain->name : '');
        
        $targetType = $item->target_type ?? 'proxy';
        $targetDest = $item->target_destination ?? ($item->ip ? "http://{$item->ip}" : 'http://127.0.0.1:8000');
        if ($targetType === 'proxy' && !preg_match('/^https?:\/\//i', $targetDest)) {
            $targetDest = "http://{$targetDest}";
        }

        $customConfig = trim($item->custom_nginx_config ?? '');
        $customConfigMode = $item->custom_config_mode ?? 'default';

        if ($customConfigMode === 'replace' && !empty($customConfig)) {
            return $this->generateApacheReplaceConfig($domainName, $customConfig);
        }

        $cloudflareIps = [
            '103.21.244.0/22', '103.31.4.0/22', '141.101.64.0/18', '108.162.192.0/18',
            '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17',
            '162.158.0.0/15', '104.16.0.0/13', '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22'
        ];

        $output = "# Auto-generated Apache VirtualHost Config by Dynomain\n";
        $output .= "# Domain: {$domainName}\n";
        $output .= "# Generated At: " . date('Y-m-d H:i:s') . "\n\n";

        $output .= "<VirtualHost *:80>\n";
        $output .= "    ServerName {$domainName}\n";
        if ($isWildcard && !str_starts_with($domainName, '*.')) {
            $output .= "    ServerAlias *.{$domainName}\n";
        }

        $output .= "\n    # Cloudflare RemoteIP Header\n";
        $output .= "    RemoteIPHeader CF-Connecting-IP\n";
        $output .= "    RemoteIPTrustedProxy " . implode(' ', $cloudflareIps) . "\n\n";

        if ($targetType === 'webroot') {
            $output .= "    DocumentRoot {$targetDest}\n";
            $output .= "    <Directory {$targetDest}>\n";
            $output .= "        Options Indexes FollowSymLinks MultiViews\n";
            $output .= "        AllowOverride All\n";
            $output .= "        Require all granted\n";
            $output .= "    </Directory>\n";
        } else {
            $output .= "    ProxyPreserveHost On\n";
            $output .= "    ProxyPass / {$targetDest}/\n";
            $output .= "    ProxyPassReverse / {$targetDest}/\n";
        }

        if (!empty($customConfig) && $customConfigMode === 'add') {
            $output .= "\n    # Custom Directives (Add Mode)\n";
            $output .= "    " . str_replace("\n", "\n    ", $customConfig) . "\n";
        }

        $output .= "</VirtualHost>\n";

        return $output;
    }

    /**
     * Generate Apache config with REPLACE mode (custom directives only).
     */
    protected function generateApacheReplaceConfig(string $domainName, string $customConfig): string
    {
        $output = "# Custom Apache Config (Replace Mode) by Dynomain\n";
        $output .= "# Domain: {$domainName}\n";
        $output .= "# Generated At: " . date('Y-m-d H:i:s') . "\n\n";

        $output .= $customConfig . "\n";

        return $output;
    }
}
