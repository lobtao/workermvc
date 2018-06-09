<?php
/**
 * Created by lobtao.
 */

use workermvc\Config;
use workermvc\Log;
use workermvc\Session;
use think\Db;
use think\Cache;
use workermvc\server\MainServer;

define('WORKERMVC_VERSION', '1.0.8');
define('THINK_START_TIME', microtime(true));
define('THINK_START_MEM', memory_get_usage());
define('EXT', '.php');
define('DS', DIRECTORY_SEPARATOR);
defined('WORKERMVC_PATH') or define('WORKERMVC_PATH', __DIR__ . DS);
define('LIB_PATH', WORKERMVC_PATH . 'src' . DS);
define('TRAIT_PATH', LIB_PATH . 'traits' . DS);
define('ENGINE_PATH', LIB_PATH . 'workerman' . DS);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DS);
defined('EXTEND_PATH') or define('EXTEND_PATH', ROOT_PATH . 'extend' . DS);
defined('VENDOR_PATH') or define('VENDOR_PATH', ROOT_PATH . 'vendor' . DS);
defined('PUBLIC_PATH') or define('PUBLIC_PATH', ROOT_PATH . 'public' . DS);
defined('RUNTIME_PATH') or define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DS);
defined('LOG_PATH') or define('LOG_PATH', RUNTIME_PATH . 'log' . DS);
defined('CACHE_PATH') or define('CACHE_PATH', RUNTIME_PATH . 'cache' . DS);
defined('TEMP_PATH') or define('TEMP_PATH', RUNTIME_PATH . 'temp' . DS);
defined('CONF_PATH') or define('CONF_PATH', ROOT_PATH . "config" . DS);
defined('LANG_PATH') or define('LANG_PATH', CONF_PATH . "lang" . DS);
defined('ROUTE_PATH') or define('ROUTE_PATH', ROOT_PATH . "route" . DS);
defined('CONF_EXT') or define('CONF_EXT', EXT);
defined('ENV_PREFIX') or define('ENV_PREFIX', 'PHP_');

if (PHP_SAPI != 'cli') {
    exit("Error: WorkerMVC 只能运行在 php cli 模式下.");
}

require_once WORKERMVC_PATH . 'common.php';
require_once WORKERMVC_PATH . 'helper.php';
//
///**
// * 配置文件初始化
// */
//Config::_init();
//
///**
// * 缓存初始化
// */
//Cache::init(Config::get('cache'));
//
///**
// * 日志初始化
// */
//Log::_init(Config::get('log'));
//
///**
// * 数据库初始化
// */
//Db::setConfig(Config::get('database'));
//
//
///**
// * Session 初始化
// */
//Session::_init(Config::get('session'));

$mainServer = new MainServer();
$mainServer::runAll();