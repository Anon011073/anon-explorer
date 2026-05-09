<?php

namespace App\Core;

class Hook {
    protected static $listeners = [];

    public static function listen($event, $callback) {
        self::$listeners[$event][] = $callback;
    }

    public static function trigger($event, ...$args) {
        $results = [];
        if (isset(self::$listeners[$event])) {
            foreach (self::$listeners[$event] as $callback) {
                $results[] = call_user_func_array($callback, $args);
            }
        }
        return $results;
    }

    public static function apply($event, $value, ...$args) {
        if (isset(self::$listeners[$event])) {
            foreach (self::$listeners[$event] as $callback) {
                $value = call_user_func($callback, $value, ...$args);
            }
        }
        return $value;
    }
}
