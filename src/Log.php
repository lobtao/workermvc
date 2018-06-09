<?php
/**
 * Created by lobtao.
 */

namespace workermvc;

/**
 * Class Log
 * @package workermvc
 *
 * @method void emergency($msg) static
 * @method void alert($msg) static
 * @method void critical($msg) static
 * @method void error($msg) static
 * @method void warning($msg) static
 * @method void notice($msg) static
 * @method void info($msg) static
 * @method void debug($msg) static
 * @method void sql($msg) static
 *
 */
class Log {
    /**
     * @var null|\think\Log
     */
    protected static $driver = null;
    /**
     * @var array 日志类型
     */
    protected static $type = ['log', 'error', 'info', 'sql', 'notice', 'alert', 'debug'];

    /**
     * Log initialization method
     *
     * @param array $configs
     * @return void
     */
    public static function _init($configs) {
        self::$driver = new \think\Log();
        self::$driver->init($configs);
    }

    /**
     * 静态方法调用
     * @access public
     * @param  string $method 调用方法
     * @param  mixed $args 参数
     * @return void
     */
    public static function __callStatic($method, $args) {
        if (in_array($method, self::$type)) {
            array_push($args, $method);

            call_user_func_array([self::$driver, 'record'], $args);
        }
    }
}