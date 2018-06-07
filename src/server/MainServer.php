<?php
/**
 * Created by lobtao.
 * Date: 2018/5/26
 * Time: 上午4:37
 */

namespace workermvc\server;

use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use workermvc\Config;
use workermvc\Dispatcher;
use workermvc\exception\FatalException;
use workermvc\exception\HttpException;
use workermvc\exception\UnknownException;
use workermvc\Log;
use workermvc\Request;
use workermvc\Response;
use workermvc\Session;
use workermvc\StaticDispatcher;

class MainServer extends BaseServer {

    /**
     * @var Worker
     */
    protected $worker;
    protected $socket = '';
    protected $protocol = 'http';
    protected $hostname = '0.0.0.0';
    protected $hostport = '9981';
    protected $count = 4;
    protected $name = 'MainServer';
    protected $max_request_restart = true;
    protected $max_request_limit = 1000;

    protected function init() {
        /**
         * 加载 worker 配置
         */
        $config = Config::get('worker');
        !isset($config['protocol']) or $this->protocol = $config['protocol'];
        !isset($config['hostname']) or $this->hostname = $config['hostname'];
        !isset($config['hostport']) or $this->hostport = $config['hostport'];
        !isset($config['count']) or $this->count = $config['count'];
        !isset($config['name']) or $this->name = $config['name'];
        !isset($config['max_request_restart']) or $this->max_request_restart = $config['max_request_restart'];
        !isset($config['max_request_limit']) or $this->max_request_limit = $config['max_request_limit'];

    }

    /**
     * @param Worker $worker
     */
    public function onWorkerStart($worker) {

    }

    /**
     * @param Worker $worker
     */
    public function onWorkerReload($worker) {

    }

    /**
     * @param TcpConnection $connection
     * @param $data
     */
    public function onMessage($connection, $data) {
        global $TW_ENV_REQUEST, $TW_ENV_RESPONSE;
        //Session auto start
        if (config("session.auto_start")) {
            Session::startSession();
        }
        //Init Request and Response Objects
        $req = new Request($data);
        $resp = new Response($connection, $req);
        $TW_ENV_REQUEST = $req;
        $TW_ENV_RESPONSE = $resp;

        try {
            //Static files dispatching
            if (StaticDispatcher::dispatch($req, $resp)) {
                return;
            };
            //Dispatching
            Dispatcher::dispatch($req->getUri(), $req, $resp);
        } catch (HttpException $e) {
            //Caught HttpException then deliver msg to browser client
            $resp->setHeader("HTTP", true, $e->getStatusCode());
            $resp->send($e->getHttpBody());
            $eDesc = describeException($e);
            Log::error($eDesc);
        } catch (FatalException $e) {
            //Caught FatalException then log error and shut down server
            $eDesc = describeException($e);
            Log::error($eDesc);
        } catch (\Throwable $e) {
            //Unknown but not Fatal Exception
            $ne = new UnknownException($e);
            $resp->setHeader("HTTP", true, $ne->getStatusCode());
            $resp->send($ne->getHttpBody());
            $eDesc = describeException($e);
            Log::error($eDesc);
        }

        if ($this->max_request_restart && !think_core_is_win()) {
            static $request_count = 0;
            if (++$request_count >= $this->max_request_limit) {
                $this->worker->stopAll();
            }
        }
    }

    /**
     * 启动服务
     */
    public static function runAll() {
        self::loadOtherServers();
        worker::runAll();
    }

    /**
     * 加载其它服务
     */
    private static function loadOtherServers() {
        if (think_core_is_win()) {
            think_core_print_error("Windows does not support multi workers!");
            return;
        }

        /**
         * 添加文件检测服务
         */
        if (Config::get('worker.debug')) {
            new FileMonitor();
        }

        /**
         * 加载自定义服务
         */
        foreach (glob(APP_PATH . "server" . DS . "*" . EXT) as $serverFile) {
            $fullClassName = "app\\server\\" . basename($serverFile, '.php');
            try {
                new $fullClassName();
            } catch (\Exception $e) {
                think_core_print_error("Failed to load: " . $fullClassName);
            }
        }
    }
}