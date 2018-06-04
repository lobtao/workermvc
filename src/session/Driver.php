<?php
/**
 * Created by lobtao.
 */

namespace workermvc\session;


interface Driver {
    public function init($config);

    public function startSession();

    public function closeSession();

    public function set($key, $value);

    public function get($key = null);

    public function has($key);

    public function delete($key);

    public function clear();
}