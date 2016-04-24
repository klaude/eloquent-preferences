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
    switch ($key) {
        case 'eloquent-preferences.table':
            return 'foo-function';
            break;

        case 'eloquent-preferences.hidden-attributes':
            return ['foo', 'function'];
            break;
    }
}
