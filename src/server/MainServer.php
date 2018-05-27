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
use workermvc\Request;
use workermvc\Response;
use workermvc\Session;
use workermvc\StaticDispatcher;

class MainServer extends BaseServer {
    /**
     * @var Worker
     */
    protected $worker;
    protected $protocol = 'http';
    protected $host = '0.0.0.0';
    protected $port = '9981';
    protected $count = 4;
    protected $name = 'MainServer';
    protected $max_request_restart = true;
    protected $max_request_limit = 1000;

    protected function init() {

        $config = Config::get('worker');
        !isset($config['protocol']) or $this->protocol = $config['protocol'];
        !isset($config['host']) or $this->host = $config['host'];
        !isset($config['port']) or $this->port = $config['port'];
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
     * @param TcpConnection $connection
     * @param $data
     */
    public function onMessage($connection, $data) {
        global $TW_ENV_REQUEST, $TW_ENV_RESPONSE;
        //Session auto start
        if(config("session.auto_start")){
            Session::startSession();
        }
        //Init Request and Response Objects
        $req = new Request($data);
        $resp = new Response($connection, $req);
        $TW_ENV_REQUEST = $req;
        $TW_ENV_RESPONSE = $resp;

        try{
            //Static files dispatching
            if(StaticDispatcher::dispatch($req, $resp)){
                return;
            };
            //Dispatching
            //$connection->send(json_encode($req));
            Dispatcher::dispatch($req->getUri(), $req, $resp);
        }catch (HttpException $e){
            //Caught HttpException then deliver msg to browser client
            $resp->setHeader("HTTP", true, $e->getStatusCode());
            $resp->send($e->getHttpBody());
            $eDesc = describeException($e);
//            Log::e($eDesc, "HttpException");
        }catch (FatalException $e){
            //Caught FatalException then log error and shut down server
            $eDesc = describeException($e);
//            Log::e($eDesc, "FatalException");
        }catch (\Throwable $e){
            //Unknown but not Fatal Exception
            $ne = new UnknownException($e);
            $resp->setHeader("HTTP", true, $ne->getStatusCode());
            $resp->send($ne->getHttpBody());
            $eDesc = describeException($e);
//            Log::e($eDesc, "UnkownException");
        }

        if ($this->max_request_restart && !think_core_is_win()) {
            static $request_count = 0;
            if (++$request_count >= $this->max_request_limit) {
                $this->worker->stopAll();
            }
        }
    }

    public static function runAll() {
        worker::runAll();
    }

}