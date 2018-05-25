<?php
/**
 * Created by lobtao.
 * Date: 2018/5/26
 * Time: 上午4:37
 */

namespace workermvc\server;

use think\worker\Server;
use Workerman\Worker;

class MainServer extends Server {
    protected $worker;
    protected $socket = '';
    protected $protocol = 'http';
    protected $host = '0.0.0.0';
    protected $port = '8080';
    protected $processes = 4;

    public function onWorkerStart() {
    }

    public function onMessage() {

    }

}