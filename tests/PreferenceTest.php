<?php

namespace KLaude\EloquentPreferences\Tests;

use KLaude\EloquentPreferences\Preference;
use PHPUnit\Framework\TestCase;

class PreferenceTest extends TestCase
{
    public function testSetTheDefaultTableName()
    {
        $this->assertEquals(Preference::DEFAULT_MODEL_PREFERENCE_TABLE, (new Preference())->getTable());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetTheTableNameByConstant()
    {
        define('MODEL_PREFERENCE_TABLE', 'foo-constant');

        $this->assertEquals('foo-constant', (new Preference())->getTable());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetTheTableNameByLaravelConfig()
    {
        // Define config() in the global namespace
        require_once __DIR__ . '/Support/helpers.php';

        $this->assertEquals('foo-function', (new Preference())->getTable());
    }

    public function testPreferencesHaveNoHiddenAttributesByDefault()
    {
        $this->assertEquals([], (new Preference())->getHidden());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetHiddenAttributesByConstant()
    {
        define('MODEL_PREFERENCE_HIDDEN_ATTRIBUTES', 'foo,constant');

        $this->assertEquals(['foo', 'constant'], (new Preference())->getHidden());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetHiddenAttributesByLaravelConfig()
    {
        // Define config() in the global namespace
        require_once __DIR__ . '/Support/helpers.php';

        $this->assertEquals(['foo', 'function'], (new Preference())->getHidden());
    }
}
