<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subdomain extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'ip', 'status', 'domain_id'];
    protected $casts = [
        'status' => 'boolean',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}
