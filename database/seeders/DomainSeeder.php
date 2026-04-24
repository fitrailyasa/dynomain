<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Subdomain;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    public function run(): void
    {
        $domains = [
            ['name' => 'test1.my.id', 'ip' => '192.168.1.1', 'status' => 1],
            ['name' => 'test2.my.id', 'ip' => '192.168.1.6', 'status' => 1],
            ['name' => 'test3.my.id', 'ip' => '192.168.1.7', 'status' => 1],
            ['name' => 'test4.my.id', 'ip' => '192.168.1.8', 'status' => 1],
            ['name' => 'test5.my.id', 'ip' => '192.168.1.9', 'status' => 1],
        ];

        foreach ($domains as $index => $domainData) {
            $domain = Domain::updateOrCreate(
                ['name' => $domainData['name']],
                $domainData
            );

            // prefix subdomain
            $prefixes = ['www', 'api', 'admin', 'app'];

            foreach ($prefixes as $i => $prefix) {
                $subdomainName = $prefix . '.' . $domain->name;

                $subdomainData = [
                    'name' => $subdomainName,
                    'ip' => '192.168.1.' . (($index * 4) + $i + 2), // auto IP biar unik
                    'status' => 1,
                    'domain_id' => $domain->id,
                ];

                Subdomain::updateOrCreate(
                    [
                        'name' => $subdomainName,
                        'domain_id' => $domain->id
                    ],
                    $subdomainData
                );
            }
        }
    }
}
