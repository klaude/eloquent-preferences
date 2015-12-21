<?php

namespace KLaude\EloquentPreferences\Tests;

use KLaude\EloquentPreferences\Preference;
use PHPUnit_Framework_TestCase;

class PreferenceTest extends PHPUnit_Framework_TestCase
{
    public function testSetTheDefaultTableName()
    {
        $this->assertEquals(Preference::DEFAULT_MODEL_PREFERENCE_TABLE, (new Preference)->getTable());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetTheTableNameByConstant()
    {
        define('MODEL_PREFERENCE_TABLE', 'foo-constant');

        $this->assertEquals('foo-constant', (new Preference)->getTable());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetTheTableNameByLaravelConfig()
    {
        // Define config() in the global namespace
        require_once __DIR__ . '/Support/helpers.php';

        $this->assertEquals('foo-function', (new Preference)->getTable());
    }
}
