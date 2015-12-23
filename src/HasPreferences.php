<?php

namespace KLaude\EloquentPreferences;

/**
 * Assign preferences to an Eloquent Model.
 *
 * Add `use HasPreferences;` to your model class to associate preferences with
 * that model.
 *
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

        if (!is_null($savedPreference)) {
            return $savedPreference->value;
        }

        if (
            property_exists($this, 'preference_defaults')
            && is_array($this->preference_defaults)
            && array_key_exists($preference, $this->preference_defaults)
        ) {
            return $this->preference_defaults[$preference];
        }

        return $defaultValue;
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
        /** @var Preference $savedPreference */
        $savedPreference = $this->preferences()->where('preference', $preference)->first();

        if (is_null($savedPreference)) {
            $this->preferences()->save(
                new Preference(['preference' => $preference, 'value' => $value])
            );
        } else{
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
}
