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
    // Re-declare methods defined in Eloquent as abstract methods to prevent IDE
    // and CI warnings.

    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $name
     * @param  string  $type
     * @param  string  $id
     * @param  string  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    /**
     * Convert a DateTime to a storable string.
     *
     * @param  \DateTime|int  $value
     * @return string
     */
    abstract public function fromDateTime($value);

    /**
     * Encode the given value as JSON.
     *
     * @param  mixed  $value
     * @return string
     */
    abstract protected function asJson($value);

    /**
     * Decode the given JSON back into an array or object.
     *
     * @param  string  $value
     * @param  bool  $asObject
     * @return mixed
     */
    abstract public function fromJson($value, $asObject = false);

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed  $value
     * @return \Carbon\Carbon
     */
    abstract protected function asDateTime($value);

    /**
     * Return a timestamp as unix timestamp.
     *
     * @param  mixed  $value
     * @return int
     */
    abstract protected function asTimeStamp($value);

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
            $value = $this->asJson($value);
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
        if ($this->hasModelDefinedDefaultValue($preference)) {
            return $this->preference_defaults[$preference];
        }

        return $defaultValue;
    }

    /**
     * Determine if a model has a default preference value defined.
     *
     * @param string $preference
     * @return bool
     */
    protected function hasModelDefinedDefaultValue($preference)
    {
        return property_exists($this, 'preference_defaults')
            && is_array($this->preference_defaults)
            && array_key_exists($preference, $this->preference_defaults);
    }

    /**
     * Determine if a model has a preference's type cast defined.
     *
     * @param string $preference
     * @param array|string|null $types
     * @return bool
     */
    protected function hasPreferenceCast($preference, $types = null)
    {
        if (
            property_exists($this, 'preference_casts')
            && is_array($this->preference_casts)
            && array_key_exists($preference, $this->preference_casts)
        ) {
            return $types
                ? in_array($this->getPreferenceCastType($preference), (array) $types, true)
                : true;
        }

        return false;
    }

    /**
     * Determine whether a preference value is Date / DateTime castable for
     * inbound manipulation.
     *
     * This logic is taken from the upsteam Eloquent model's isDateCastable()
     * method
     *
     * @see Model::isDateCastable()
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
     * This logic is taken from the upsteam Eloquent model's isJsonCastable()
     * method
     *
     * @see Model::isJsonCastable()
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
     * @return string
     */
    protected function getPreferenceCastType($preference)
    {
        return trim(strtolower($this->preference_casts[$preference]));
    }

    /**
     * Cast a preference value's type.
     *
     * This logic is taken from the upsteam Eloquent model's castAttribute()
     * method
     *
     * @see Model::castAttribute()
     * @see https://laravel.com/docs/5.2/eloquent-mutators#attribute-casting
     * @param string $preference
     * @param mixed $value
     * @return mixed
     */
    protected function castPreferenceValue($preference, $value)
    {
        if (!$this->hasPreferenceCast($preference)) {
            return $value;
        }

        switch ($this->getPreferenceCastType($preference)) {
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
                return $this->fromJson($value, true);
            case 'array':
            case 'json':
                return $this->fromJson($value);
            case 'collection':
                return new BaseCollection($this->fromJson($value));
            case 'date':
            case 'datetime':
                return $this->asDateTime($value);
            case 'timestamp':
                return $this->asTimeStamp($value);
            default:
                return $value;
        }
    }
}
