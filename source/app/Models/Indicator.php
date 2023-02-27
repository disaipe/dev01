<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string code
 * @property string name
 * @property array schema
 * @property boolean published
 */
class Indicator extends Model
{
    protected $fillable = [
        'code',
        'name',
        'schema',
        'published'
    ];

    protected $casts = [
        'schema' => 'json',
        'published' => 'bool'
    ];
}
