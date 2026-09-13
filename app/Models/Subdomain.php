<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subdomain extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'ip',
        'status',
        'domain_id',
        'webserver_type',
        'target_type',
        'target_destination',
        'redirect_url',
        'server_id',
        'ssl_type',
        'ssl_cert_path',
        'ssl_key_path',
        'custom_nginx_config',
        'custom_config_mode',
        'published_at',
        'publish_status',
        'publish_log',
    ];

    protected $casts = [
        'status' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
