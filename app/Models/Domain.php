<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'ip',
        'status',
        'is_wildcard',
        'webserver_type',
        'target_type',
        'target_destination',
        'server_id',
        'ssl_type',
        'ssl_cert_path',
        'ssl_key_path',
        'custom_nginx_config',
        'published_at',
        'publish_status',
        'publish_log',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_wildcard' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function subdomains()
    {
        return $this->hasMany(Subdomain::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
