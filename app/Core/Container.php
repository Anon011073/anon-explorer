<?php

namespace App\Core;

class Container {
    protected $instances = [];

    public function set($name, $instance) {
        $this->instances[$name] = $instance;
    }

    public function get($name) {
        if (!isset($this->instances[$name])) {
            throw new \Exception("Service not found: {$name}");
        }
        return $this->instances[$name];
    }
}
