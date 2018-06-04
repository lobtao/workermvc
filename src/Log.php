<?php
/**
 * Created by lobtao.
 */

namespace workermvc;

/**
 * Class Log
 * @package workermvc
 *
 * @method void log($msg) static 记录一般日志
 * @method void error($msg) static 记录错误日志
 * @method void info($msg) static 记录一般信息日志
 * @method void sql($msg) static 记录 SQL 查询日志
 * @method void notice($msg) static 记录提示日志
 * @method void alert($msg) static 记录报警日志
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