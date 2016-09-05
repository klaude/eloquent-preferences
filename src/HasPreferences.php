<?php

namespace KLaude\EloquentPreferences;

use Illuminate\Support\Collection as BaseCollection;

/**
 * Assign preferences to an Eloquent Model.
 *
 * Add `use HasPreferences;` to your model class to associate preferences with
 * that model.
 *
 * @property array $preference_defaults
 * @property array $preference_casts
 * @property \Illuminate\Database\Eloquent\Collection|\KLaude\EloquentPreferences\Preference[] $preferences
 */
trait HasPreferences
{
    /**
     * A model can have many preferences.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function preferences()
    {
        return $this->morphMany(Preference::class, 'preferable');
    }

    /**
     * Retrieve a single preference by name.
     *
     * @param string $preference
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getPreference($preference, $defaultValue = null)
    {
        $savedPreference = $this->preferences()->where('preference', $preference)->first();

        $value = is_null($savedPreference)
            ? $this->getDefaultValue($preference, $defaultValue)
            : $savedPreference->value;

        return $this->castPreferenceValue($preference, $value);
    }

    /**
     * A possibly more human-readable way to retrieve a single preference by
     * name.
     *
     * @see getPreference()
     * @param string $preference
     * @param mixed $defaultValue
     * @return mixed
     */
    public function prefers($preference, $defaultValue = null)
    {
        return $this->getPreference($preference, $defaultValue);
    }

    /**
     * Set an individual preference value.
     *
     * @param string $preference
     * @param mixed $value
     * @return self
     */
    public function setPreference($preference, $value)
    {
        // Serialize date and JSON-like preference values.
        if ($this->isPreferenceDateCastable($preference)) {
            $value = $this->fromDateTime($value);
        } elseif ($this->isPreferenceJsonCastable($preference)) {
            $value = method_exists($this, 'asJson') ? $this->asJson($value) : json_encode($value);
        }

        /** @var Preference $savedPreference */
        $savedPreference = $this->preferences()->where('preference', $preference)->first();

        if (is_null($savedPreference)) {
            $this->preferences()->save(
                new Preference(['preference' => $preference, 'value' => $value])
            );
        } else {
            $savedPreference->update(['value' => $value]);
        }

        return $this;
    }

    /**
     * Set multiple preference values.
     *
     * @param array|\Illuminate\Support\Collection $preferences
     * @return self
     */
    public function setPreferences($preferences = [])
    {
        foreach ($preferences as $preference => $value) {
            $this->setPreference($preference, $value);
        }

        return $this;
    }

    /**
     * Delete one preference.
     *
     * @param string $preference
     * @return self
     */
    public function clearPreference($preference)
    {
        $this->preferences()->where('preference', $preference)->delete();

        return $this;
    }

    /**
     * Delete many preferences.
     *
     * @param array|\Illuminate\Support\Collection $preferences $preferences
     * @return self
     */
    public function clearPreferences($preferences = [])
    {
        $this->preferences()->whereIn('preference', $preferences)->delete();

        return $this;
    }

    /**
     * Delete all preferences.
     *
     * @return self
     */
    public function clearAllPreferences()
    {
        $this->preferences()->delete();

        return $this;
    }

    /**
     * Retrieve a preference's default value.
     *
     * Look in the model first, otherwise return the user specified default
     * value.
     *
     * @param string $preference
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function getDefaultValue($preference, $defaultValue = null)
    {
        if ($this->hasPreferenceDefault($preference)) {
            return $this->preference_defaults[$preference];
        }

        return $defaultValue;
    }

    /**
     * Determine if a model has preference defaults defined.
     *
     * @return bool
     */
    protected function hasPreferenceDefaults()
    {
        return property_exists($this, 'preference_defaults') && is_array($this->preference_defaults);
    }

    /**
     * Determine if a model has a default preference value defined.
     *
     * @param string $preference
     * @return bool
     */
    protected function hasPreferenceDefault($preference)
    {
        return $this->hasPreferenceDefaults() && array_key_exists($preference, $this->preference_defaults);
    }

    /**
     * Determine if a model has preference casts defined.
     *
     * @return bool
     */
    protected function hasPreferenceCasts()
    {
        return property_exists($this, 'preference_casts') && is_array($this->preference_casts);
    }

    /**
     * Determine if a model has a preference's type cast defined.
     *
     * @param string $preference
     * @param array $types
     * @return bool
     */
    protected function hasPreferenceCast($preference, array $types = null)
    {
        if ($this->hasPreferenceCasts() && array_key_exists($preference, $this->preference_casts)) {
            return $types
                ? in_array($this->getPreferenceCastType($preference), $types, true)
                : true;
        }

        return false;
    }

    /**
     * Determine whether a preference value is Date / DateTime castable for
     * inbound manipulation.
     *
     * @param string $preference
     * @return bool
     */
    protected function isPreferenceDateCastable($preference)
    {
        return $this->hasPreferenceCast($preference, ['date', 'datetime']);
    }

    /**
     * Determine whether a preference value is JSON castable for inbound
     * manipulation.
     *
     * @param string $preference
     * @return bool
     */
    protected function isPreferenceJsonCastable($preference)
    {
        return $this->hasPreferenceCast($preference, ['array', 'json', 'object', 'collection']);
    }

    /**
     * Retrieve the type of variable to cast a preference value to.
     *
     * @param string $preference
     * @return string|null
     */
    protected function getPreferenceCastType($preference)
    {
        return $this->hasPreferenceCast($preference)
            ? trim(strtolower($this->preference_casts[$preference]))
            : null;
    }

    /**
     * Cast a preference value's type.
     *
     * @param string $preference
     * @param mixed $value
     * @return mixed
     */
    protected function castPreferenceValue($preference, $value)
    {
        $castTo = $this->getPreferenceCastType($preference);

        // Cast Eloquent >= 5.0 compatible types.
        switch ($castTo) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'object':
                return method_exists($this, 'fromJson')
                    ? $this->fromJson($value, true)
                    : json_decode($value);
            case 'array':
            case 'json':
                return method_exists($this, 'fromJson')
                    ? $this->fromJson($value)
                    : json_decode($value, true);
            case 'collection':
                return new BaseCollection(
                    method_exists($this, 'fromJson')
                        ? $this->fromJson($value)
                        : json_decode($value, true)
                );
        }

        // Cast Eloquent >= 5.1 compatible types.
        if (method_exists($this, 'asDateTime')) {
            switch ($castTo) {
                case 'date':
                case 'datetime':
                    return $this->asDateTime($value);
            }
        }

        // Cast Eloquent >= 5.2 compatible types.
        if (method_exists($this, 'asTimeStamp')) {
            switch ($castTo) {
                case 'timestamp':
                    return $this->asTimeStamp($value);
            }
        }

        return $value;
    }
}
