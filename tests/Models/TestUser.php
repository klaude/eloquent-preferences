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
}
