# Preferences for Laravel Eloquent models

[![Build Status](https://travis-ci.org/klaude/eloquent-preferences.png)](https://travis-ci.org/klaude/eloquent-preferences)

Use this library to bind multiple key/value pair preferences to your application's Eloquent models. Preferences are stored in your application's database so they can be easily stored and queried for. This library currently only supports Eloquent 5.x installed either standalone or as a part of the full Laravel framework. Issues and pull requests are welcome!

* [Installation](#installation)
  * [Configuring In Laravel](#configuring-in-laravel)
  * [Configuring Without Laravel](#configuring-without-laravel)
* [Usage](#usage)
  * [Helper Methods](#helper-methods)
    * [Retrieving Preferences](#retrieving-preferences)
    * [Setting Preferences](#setting-preferences)
    * [Removing Preferences](#removing-preferences)
  * [Default Preference Values](#default-preference-values)

<a name="installation"></a>
## Installation

Run `composer require klaude/eloquent-preferences` to download and install the library.

<a name="configuring-in-laravel"></a>
### Configuring In Laravel

1) Add `EloquentPreferencesServiceProvider` to `config/app.php`:

```php
// ...

return [

    // ...

    'providers' => [

        // ...

        KLaude\EloquentPreferences\EloquentPreferencesServiceProvider::class,
    ],

    // ...
];
```

2) Install the configuration and database migration files:

```
$ php artisan vendor:publish
```

3) Model preferences are stored in the "model_preferences" database table by default. If you would like to use a different table then edit the "table" entry in `config/eloquent-preferences.php`.

4) Install the model preferences database:

```
$ php artisan migrate
```

<a name="configuring-without-laravel"></a>
### Configuring Without Laravel

1) Model preferences are stored in the "model_preferences" database table by default. If you would like to use a different table then define the `MODEL_PREFERENCE_TABLE` constant at your project's point of entry with your preferred table name.

2) Install the model preferences database. There are a number of ways to do this outside of Laravel. Here's the schema blueprint to apply:

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use KLaude\EloquentPreferences\Preference;

// ...

Model::getConnectionResolver()
    ->connection()
    ->getSchemaBuilder()
    ->create((new Preference)->getQualifiedTableName(), function (Blueprint $table) {
        $table->increments('id');
        $table->string('preference');
        $table->string('value');
        $table->morphs('preferable');
        $table->timestamps();
    });

```

<a name="usage"></a>
## Usage

Add the `HasPreferences` trait to the Eloquent models that you would like to have related preferences.

```php
use KLaude\EloquentPreferences\HasPreferences;

// ...

class MyModel extends Model
{
    use HasPreferences;

    // ...
}
```

This builds a polymorphic has-many relationship called "preferences" that you can query on your model like any other Eloquent relationship. Model preferences are modeled in the `KLaude\EloquentPreferences\Preference` class. A preference object has `preference`, `value`, and Eloquent's built-in `created_at` and `updated_at` attributes. The `HasPreferences` trait can be used by any number of model classes in your application.

```php
// Retrieving preferences via Eloquent
/** @var KLaude\EloquentPreferences\Preference $myPreference */
$myPreference = MyModel::find($someId)->prefrences()->where('preference', 'my-preference')->get();

// Saving preferences via Eloquent
$preference = new Preference;
$preference->preference = 'some preference';
$preference->value = 'some value';
$myModel->preferences()->save($preference);
```

Eloquent queries can be run directly on the `Preference` class as well.

```php
/** @var Illuminate\Database\Eloquent\Collection|KLaude\EloquentPreferences\Preference[] $preferences */
$preferences = Preference::whereIn('preference', ['foo', 'bar'])->orderBy('created_at')->get();
```

<a name="helper-methods"></a>
### Helper Methods

The `HasPreferences` trait has a number of helper methods to make preference management a little easier.

<a name="retrieving-preferences"></a>
#### Retrieving Preferences

Call the `getPreference($preferenceName)` or `prefers($preferenceName)` methods to retrieve that preference's value.

```php
$numberOfFoos = $myModel->getPreference('number-of-foos');

$myModel->prefers('Star Trek over Star Wars') ? liveLongAndProsper() : theForceIsWithYou();
```

<a name="setting-preferences"></a>
#### Setting Preferences

Call the `setPreference($name, $value)` or `setPreferences($arrayOfNamesAndValues)` methods to set your model's preference values. Setting a preference either creates a new preference row if the preference doesn't exist or updates the existing preference with the new value.

```php
$myModel->setPreference('foo', 'bar');

$myModel->setPreferences([
    'foo' => 'bar',
    'bar' => 'baz',
]);
```

<a name="removing-preferences"></a>
#### Removing Preferences

Call the `clearPreference($preferenceName)`, `clearPreferences($arrayOfPreferenceNames)`, or `clearAllPreferences()` methods to remove one, many, or all preferences from a model. Clearing preferences removes their associated rows from the preferences table.

```php
$myModel->clearPreference('some preference');

$myModel->clearPreferences(['some preference', 'some other preference']);

$myModel->clearAllPreferences();
```

<a name="default-preference-values"></a>
### Default Preference Values

By default, `getPreference()` and `prefers()` return `null` if the preference is not stored in the database. There are two ways to declare default preference values:

1) Use an optional second parameter to `getPreference()` and `prefers()` to define a default value per call. If the preference is not stored in the database then the default value is returned.

```php
// $myPreference = 'some default value'
$myPreference = $myModel->getPreference('unknown preference', 'some default value');
```

2) Avoid requiring extra parameters to every `getPreference()` and `prefers()` call by declaring a protected `$preference_defaults` array in your model containing a key/value pair of preference names and their default values. If the preference is not stored in the database but is defined in `$preference_defaults` then the value in `$preference_defaults` is returned. If neither of these exist then optional default value parameter or `null` is returned.

```php
class MyModel extends Model
{
    use HasPreferences;
    
    // ...
    
    protected $preference_defaults = [
        'my-default-preference' => 'my-default-value',
    ];
}

// ...

// $myPreference = 'my-default-value'
$myPreference = $myModel->getPreference('my-default-preference');

// $myPreference = 'fallback value'
$myPreference = $myModel->getPreference('my-unstored-preference', 'fallback value');
```
