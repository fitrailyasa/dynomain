<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'ip', 'status'];
    protected $casts = [
        'status' => 'boolean',
    ];

    public function subdomains()
    {
        return $this->hasMany(Subdomain::class);
    }
}
