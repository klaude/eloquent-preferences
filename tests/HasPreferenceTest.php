<?php

namespace KLaude\EloquentPreferences\Tests;

use Carbon\Carbon;
use CreateModelPreferencesTable;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Collection;
use KLaude\EloquentPreferences\Preference;
use KLaude\EloquentPreferences\Tests\Models\TestUser;
use KLaude\EloquentPreferences\Tests\Support\ConnectionResolver;
use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * This test's structure is based off the Laravel Framework's SoftDeletes trait
 * test.
 *
 * @see https://github.com/laravel/framework/blob/5.1/tests/Database/DatabaseEloquentSoftDeletesIntegrationTest.php
 */
class HasPreferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * A test user model with preferences.
     *
     * @var TestUser
     */
    protected $testUser;

    /**
     * Bootstrap Eloquent.
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        Eloquent::setConnectionResolver(new ConnectionResolver);
        Eloquent::setEventDispatcher(new Dispatcher);
    }

    /**
     * Tear down Eloquent.
     */
    public static function tearDownAfterClass()
    {
        Eloquent::unsetEventDispatcher();
        Eloquent::unsetConnectionResolver();
    }

    /**
     * Set up the test database schema and data.
     */
    public function setUp()
    {
        $this->schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        (new CreateModelPreferencesTable)->up();

        $this->testUser = TestUser::create(['id' => 1, 'email' => 'johndoe@example.org']);
    }

    /**
     * Tear down the database schema.
     */
    public function tearDown()
    {
        $this->schema()->drop('users');
        $this->schema()->drop(Preference::DEFAULT_MODEL_PREFERENCE_TABLE);
    }

    /**
     * Get a database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function connection()
    {
        return Eloquent::getConnectionResolver()->connection();
    }

    /**
     * Get a schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function schema()
    {
        return $this->connection()->getSchemaBuilder();
    }

    public function testModelsHavePreferenceRelationship()
    {
        $this->assertInstanceOf(MorphMany::class, $this->testUser->preferences());
        $this->assertEmpty($this->testUser->preferences);
    }

    public function testPreferencesHaveMorphToRelationship()
    {
        $this->testUser->setPreference('preference', 'value');

        $preferable = Preference::find(1)->preferable;

        $this->assertInstanceOf(TestUser::class, $preferable);
        $this->assertEquals('johndoe@example.org', $preferable->email);
    }

    public function testSetPreferences()
    {
        $result = $this->testUser->setPreferences([
            'preference1' => 'value1',
            'preference2' => 'value2',
        ]);

        $preferences = $this->testUser->preferences;

        $this->assertInstanceOf(TestUser::class, $result);

        $this->assertEquals('value1', $this->testUser->getPreference('preference1'));
        $this->assertEquals('value2', $this->testUser->getPreference('preference2'));
        $this->assertEquals('value1', $this->testUser->prefers('preference1'));
        $this->assertEquals('value2', $this->testUser->prefers('preference2'));

        $this->assertCount(2, $preferences);
        $this->assertInstanceOf(Preference::class, $preferences[0]);
        $this->assertInstanceOf(Preference::class, $preferences[1]);
        $this->assertEquals('preference1', $preferences[0]->preference);
        $this->assertEquals('preference2', $preferences[1]->preference);
        $this->assertEquals('value1', $preferences[0]->value);
        $this->assertEquals('value2', $preferences[1]->value);
    }

    public function testGetModelDefinedDefaultValues()
    {
        $this->assertEquals('defined by model', $this->testUser->getPreference('model defined default'));
    }

    public function testGetMethodDefinedDefaultValues()
    {
        $this->assertEquals('some default', $this->testUser->getPreference('nonexistent', 'some default'));
    }

    public function testOverridePreferences()
    {
        $this->testUser->setPreference('preference', 'value1');
        $result = $this->testUser->setPreference('preference', 'value2');

        $this->assertInstanceOf(TestUser::class, $result);
        $this->assertCount(1, $this->testUser->preferences);
        $this->assertEquals('value2', $this->testUser->getPreference('preference'));
    }

    public function testClearOnePreference()
    {
        $this->testUser->setPreferences([
            'preference1' => 'value1',
            'preference2' => 'value2',
        ]);

        $result = $this->testUser->clearPreference('preference1');

        $this->assertInstanceOf(TestUser::class, $result);
        $this->assertCount(1, $this->testUser->preferences);
        $this->assertNull($this->testUser->getPreference('preference1'));
        $this->assertEquals('value2', $this->testUser->getPreference('preference2'));
    }

    public function testClearManyPreferences()
    {
        $this->testUser->setPreferences([
            'preference1' => 'value1',
            'preference2' => 'value2',
            'preference3' => 'value3',
        ]);

        $result = $this->testUser->clearPreferences(['preference1', 'preference2']);

        $this->assertInstanceOf(TestUser::class, $result);
        $this->assertCount(1, $this->testUser->preferences);
        $this->assertNull($this->testUser->getPreference('preference1'));
        $this->assertNull($this->testUser->getPreference('preference2'));
        $this->assertEquals('value3', $this->testUser->getPreference('preference3'));
    }

    public function testClearAllPreferences()
    {
        $this->testUser->setPreferences([
            'preference1' => 'value1',
            'preference2' => 'value2',
        ]);

        $result = $this->testUser->clearAllPreferences();

        $this->assertInstanceOf(TestUser::class, $result);
        $this->assertEmpty($this->testUser->preferences);
    }

    /**
     * @return array
     */
    public function provideInternalTypesInputsAndOutputs()
    {
        $date = Carbon::now();

        return [
            'int cast to int' => ['int-preference', 1234, 'int', 1234],
            'string cast to int' => ['int-preference', '1234', 'int', 1234],
            'integer' => ['integer-preference', 1234, 'int', 1234],
            'real' => ['real-preference', 12.34, 'float', 12.34],
            'double' => ['double-preference', 12.34, 'float', 12.34],
            'float' => ['float-preference', 12.34, 'float', 12.34],
            'string' => ['string-preference', 'foo', 'string', 'foo'],
            'bool true cast to bool' => ['bool-preference', true, 'bool', true],
            'bool false cast to bool' => ['bool-preference', false, 'bool', false],
            'int true cast to bool' => ['bool-preference', 1, 'bool', true],
            'array cast to array' => ['array-preference', [1, 2], 'array', [1, 2]],
            'json cast to array' => ['json-preference', [1, 2], 'array', [1, 2]],
            'timestamp' => ['timestamp-preference', $date, 'int', $date->timestamp],
            'unknown types don\'t get cast' => ['undefined-type-preference', '1234', 'string', '1234'],
        ];
    }

    /**
     * @dataProvider provideInternalTypesInputsAndOutputs
     * @param string $preference
     * @param int|float|string|bool $input
     * @param string $expectedInternalType
     * @param int|float|string|bool $expectedOutput
     */
    public function testCastInternalTypeValues($preference, $input, $expectedInternalType, $expectedOutput)
    {
        $this->testUser->setPreference($preference, $input);
        $value = $this->testUser->getPreference($preference);

        $this->assertInternalType($expectedInternalType, $value);
        $this->assertEquals($expectedOutput, $value);
    }

    /**
     * @return array
     */
    public function provideObjectTypesInputsAndOutputs()
    {
        $object = new stdClass;
        $object->foo = 'bar';
        $collection = new Collection(['foo']);
        $date = Carbon::now();

        return [
            'object' => ['object-preference', $object, 'stdClass', $object],
            'collection' => ['collection-preference', $collection, Collection::class, $collection],
            'date' => ['date-preference', $date, Carbon::class, $date],
            'datetime' => ['datetime-preference', $date, Carbon::class, $date],
        ];
    }

    /**
     * @dataProvider provideObjectTypesInputsAndOutputs
     * @param string $preference
     * @param int|float|string|bool $input
     * @param string $expectedClass
     * @param int|float|string|bool $expectedOutput
     */
    public function testCastObjectTypeValues($preference, $input, $expectedClass, $expectedOutput)
    {
        $this->testUser->setPreference($preference, $input);
        $value = $this->testUser->getPreference($preference);

        $this->assertInstanceOf($expectedClass, $value);
        $this->assertEquals($expectedOutput, $value);
    }
}
