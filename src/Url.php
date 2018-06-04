<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace workermvc;

class Url {
    // 生成URL地址的root
    protected static $root;
    protected static $bindCheck;

    /**
     * URL生成 支持路由反射
     * @param string $url 路由地址
     * @param string|array $vars 参数（支持数组和字符串）a=val&b=val2... ['a'=>'val1', 'b'=>'val2']
     * @param string|bool $suffix 伪静态后缀，默认为true表示获取配置值
     * @param boolean|string $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    public static function build($url = '', $vars = '', $suffix = true, $domain = false) {
        // 参数转为字符串
        if (is_array($vars)) {
            $vars = http_build_query($vars);
        }

        $controller = trim($url, '/');
        $appNameSpace = Config::get('think.default_app');
        $appNameSpace = is_null($appNameSpace) ? "index" : $appNameSpace;

        $controllerSep = explode("/", $controller);
        if (sizeof($controllerSep) == 2) {
            $url = $appNameSpace . '/' . $url;
        }

        // 伪静态处理
        if ($suffix) {
            $url_html_suffix = Config::get('think.url_html_suffix');
            $url = isset($url_html_suffix) ? $url . $url_html_suffix . '?' . $vars : $url . '?' . $vars;
        } else {
            $url = $url . '?' . $vars;
        }

        $scheme = Config::get('think.is_https') ? 'https://' : 'http://';
        global $TW_ENV_REQUEST;
        if ($domain) {
            $url = $scheme . $TW_ENV_REQUEST->getHostname() . '/' . $url;
        } else {
            $url = '/' . $url;
        }
        return $url;
    }

}
