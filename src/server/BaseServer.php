<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace workermvc\server;

use Workerman\Worker;

/**
 * Worker控制器扩展类
 */
abstract class BaseServer {
    protected $worker;
    protected $socket = '';
    protected $protocol = 'http';
    protected $hostname = '0.0.0.0';
    protected $hostport = '9981';
    protected $count = 4;
    protected $name = 'BaseServer';

    /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        // 初始化
        $this->init();
        // 实例化 Websocket 服务
        $this->worker = new Worker($this->socket ?: ($this->protocol ? $this->protocol . '://' . $this->hostname . ':' . $this->hostport : ''));
        // 设置进程数
        $this->worker->count = $this->count;
        $this->worker->name = $this->name;

        // 设置回调
        foreach (['onWorkerStart', 'onConnect', 'onMessage', 'onClose', 'onError', 'onBufferFull', 'onBufferDrain', 'onWorkerStop', 'onWorkerReload'] as $event) {
            if (method_exists($this, $event)) {
                $this->worker->$event = [$this, $event];
            }
        }
    }

    protected function init() {
    }

}
