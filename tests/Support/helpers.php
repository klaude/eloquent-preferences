<?php

/**
 * Mock Laravel's config() to test overriding the model preferences table via
 * Laravel.
 *
 * @param string $key
 * @param string $default
 * @return string
 */
function config($key = null, $default = null)
{
    return 'foo-function';
}
