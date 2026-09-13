<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class GithubSsh extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'github_ssh';

    protected $fillable = [
        'name',
        'username',
        'pat',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'pat' => 'encrypted',
    ];
}
