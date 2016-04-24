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
        $this->overrideHidden();

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

    /**
     * Set hidden preference attributes.
     *
     * Declare hidden from JSON attributes for preferences either via Laravel
     * config or by a comma-separated string constant
     * MODEL_PREFERENCE_HIDDEN_ATTRIBUTES.
     *
     * @see https://laravel.com/docs/5.2/eloquent-serialization#hiding-attributes-from-json
     */
    protected function overrideHidden()
    {
        if (function_exists('config')) {
            return $this->setHidden(config('eloquent-preferences.hidden-attributes', $this->getHidden()));
        }

        if (defined('MODEL_PREFERENCE_HIDDEN_ATTRIBUTES')) {
            return $this->setHidden(explode(',', MODEL_PREFERENCE_HIDDEN_ATTRIBUTES));
        }

        return $this;
    }
}
