<?php

namespace KLaude\EloquentPreferences;

use Illuminate\Database\Eloquent\Model;

/**
 * A simple key/value preference model.
 *
 * @property int $id
 * @property string $preference
 * @property string $value
 * @property int $preferable_id
 * @property string $preferable_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Preference extends Model
{
    const DEFAULT_MODEL_PREFERENCE_TABLE = 'model_preferences';

    protected $fillable = [
        'preference',
        'value',
    ];

    /**
     * Build a new preference model, overriding the model's table name if
     * applicable.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setTable($this->getQualifiedTableName());

        parent::__construct($attributes);
    }

    /**
     * Get all of the owning preferable models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function preferable()
    {
        return $this->morphTo();
    }

    /**
     * Determine the name of the model preferences table.
     *
     * @return string
     */
    public function getQualifiedTableName()
    {
        // Check if the user overloaded the table name via Laravel config.
        if (function_exists('config')) {
            return config('eloquent-preferences.table', self::DEFAULT_MODEL_PREFERENCE_TABLE);
        }

        // Check if the user overloaded the table name via constant.
        if (defined('MODEL_PREFERENCE_TABLE')) {
            return MODEL_PREFERENCE_TABLE;
        }

        // Otherwise use the default.
        return self::DEFAULT_MODEL_PREFERENCE_TABLE;
    }
}
