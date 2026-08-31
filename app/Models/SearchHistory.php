<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    use HasFactory;

    protected $table = 'search_histories';

    protected $fillable = [
        'type',
        'query',
        'title',
        'result_json',
        'client_ip',
        'status'
    ];

    protected $casts = [
        'result_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
