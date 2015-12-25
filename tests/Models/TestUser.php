<?php

namespace KLaude\EloquentPreferences\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use KLaude\EloquentPreferences\HasPreferences;

/**
 * A simple user model that has preferences.
 *
 * @property integer $id
 * @property string $email
 * @property \Illuminate\Database\Eloquent\Collection|\KLaude\EloquentPreferences\Preference[] $preferences
 */
class TestUser extends Model
{
    use HasPreferences;

    protected $table = 'users';
    protected $guarded = [];
    public $timestamps = false;

    protected $preference_defaults = [
        'model defined default' => 'defined by model',
    ];

    protected $preference_casts = [
        'int-preference' => 'int',
        'integer-preference' => 'integer',
        'real-preference' => 'real',
        'float-preference' => 'float',
        'double-preference' => 'double',
        'string-preference' => 'string',
        'bool-preference' => 'bool',
        'boolean-preference' => 'boolean',
        'object-preference' => 'object',
        'array-preference' => 'array',
        'json-preference' => 'json',
        'collection-preference' => 'collection',
        'date-preference' => 'date',
        'datetime-preference' => 'datetime',
        'timestamp-preference' => 'timestamp',
        'undefined-type-preference' => 'undefined',
    ];
}
