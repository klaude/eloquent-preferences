<?php

namespace KLaude\EloquentPreferences\Tests\Support;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\SQLiteConnection;
use PDO;

/**
 * A test connection resolver
 *
 * Borrow Laravel's SoftDeletesDatabaseIntegrationTestConnectionResolver class
 * to use as an in-memory SQLite test connection resolver.
 *
 * @see https://github.com/laravel/framework/blob/5.3/tests/Database/DatabaseEloquentSoftDeletesIntegrationTest.php
 */
class ConnectionResolver implements ConnectionResolverInterface
{
    /**
     * @var \Illuminate\Database\SQLiteConnection
     */
    protected $connection;

    /**
     * Get a database connection instance.
     *
     * @param  string  $name
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function connection($name = null)
    {
        if (isset($this->connection)) {
            return $this->connection;
        }

        return $this->connection = new SQLiteConnection(new PDO('sqlite::memory:'));
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return 'default';
    }

    /**
     * Set the default connection name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultConnection($name)
    {
        //
    }
}
