<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace workermvc;


use think\Template;

abstract class Controller
{
    /**
     * @var Request
     */
    protected $req;
    /**
     * @var Response
     */
    protected $resp;
    /**
     * @var null|Template
     */
    protected $view = null;
    protected $beforeActionList = [];

    /**
     * Controller constructor.
     * @param Request $req
     * @param Response $resp
     */
    public function __construct(Request $req, Response $resp) {
        $this->req = $req;
        $this->resp = $resp;
        if (!is_null($req->controllerInfo)) {
            $appName = $req->controllerInfo->appNameSpace;
            $controllerName = $req->controllerInfo->controllerNameSpace;
            $methodName = $req->controllerInfo->methodName;
            try {
                $config = Config::get('template');
                $config['cache_path'] = APP_PATH . '..' . DS . 'runtime' . DS . 'cache' . DS . 'view' . DS;
                $this->view = new Template($config);
            } catch (\Exception $ignored) {
            }
        }
    }

    /**
     * User defined Controller initialization method, ready for override
     *
     * @return void
     */
    public function _init() {
    }

    /**
     * First called before any user defined method is called
     * @param $method
     */
    public function _beforeAction($method) {
        foreach ($this->beforeActionList as $key => $value) {
            if (is_numeric($key)) {
                $this->$value($this->req, $this->resp);
            } else {
                $go = true;
                if (isset($value["except"]) && think_core_in_array_or_string($method, $value["except"])) {
                    $go = false;
                }
                if (isset($value["only"])) {
                    $go = false;
                    if (think_core_in_array_or_string($method, $value["only"])) {
                        $go = true;
                    }
                }
                if ($go) {
                    $this->$key($this->req, $this->resp);
                }
            }
        }
    }

    /**
     * Assign a variable value or multiple values for the bound View
     * @param $name
     * @param null $value
     */
    public function assign($name, $value = null) {
        if ($this->view) {
            $this->view->assign($name, $value);
        }
    }

    /**
     * @param null $template
     * @param array $vars
     * @param array $config
     * @return null|string
     * @throws \Error
     * @throws \Exception
     */
    public function fetch($template = null, $vars = [], $config = []) {
        //有模块情况
        if (strpos($template, '@')) {
            list($module, $template) = explode('@', $template);
        }
        //空情况
        if (!isset($template) || empty($template)) {
            $template = $this->req->controllerInfo->controllerNameSpace . DS . $this->req->controllerInfo->methodName;
        }
        //只输入模板名情况
        if (strpos($template, '/') == 0) {
            $template = $this->req->controllerInfo->controllerNameSpace . DS . $template;
        }
        $template = strtolower($template);
        if ($this->view) {
            // 页面缓存
            ob_start();
            ob_implicit_flush(0);
            try {
                $this->view->config([
                    'view_path' => APP_PATH . (isset($module) ? $module : Config::get('think.default_app')) . DS . 'view' . DS
                ]);
                $this->view->fetch($template, $vars, $config);
            } catch (\Error $e) {
                ob_end_clean();
                throw $e;
            }
            // 获取并清空缓存
            $content = ob_get_clean();
            return $content;
        } else {
            return null;
        }
    }

    /**
     * Render and send html output to the client
     * @param null $template
     * @throws \Error
     * @throws \Exception
     */
    public function render($template = null) {
        if ($this->view) {
            $this->resp->send($this->fetch($template));
        }
    }

    /**
     * Render and send html output to the client, alias for `render`
     * @param null $template
     */
    public function display($template = null) {
        $this->render($template);
    }

    /**
     * Get the bound View
     * @return null|Template
     */
    public function getView() {
        return $this->view;
    }
}