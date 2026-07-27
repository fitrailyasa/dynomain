<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Server extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'host',
        'port',
        'username',
        'auth_type',
        'password',
        'private_key',
        'webserver_type',
        'config_path',
        'symlink_path',
        'reload_command',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'port' => 'integer',
        'password' => 'encrypted',
        'private_key' => 'encrypted',
    ];

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function subdomains()
    {
        return $this->hasMany(Subdomain::class);
    }
}
