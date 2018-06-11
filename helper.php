<?php
/**
 * Created by lobtao.
 */

use workermvc\Config;
use workermvc\Lang;
use workermvc\Filter;
use workermvc\Session;
use think\Cache;

if (!function_exists('config')) {
    function config($name = '', $value = null, $range = '')
    {
        $range = $range ?: Config::$range;

        if (is_null($value) && is_string($name)) {
            return 0 === strpos($name, '?') ? Config::has(substr($name, 1), $range) : Config::get($name, $range);
        } else {
            return Config::set($name, $value, $range);
        }
    }
}

if (!function_exists('filter')) {
    function filter($body)
    {
        return Filter::filt($body);
    }
}

if (!function_exists('json')) {
    function json($body)
    {
        return json_encode($body);
    }
}

if (!function_exists('xml')) {
    function xml($body)
    {
        $root = config("think.xml_root_node");
        $item = config("think.xml_item_node");
        $attr = config("think.xml_root_attr");
        $id = config("think.xml_item_key");
        $encoding = "utf-8";
        return think_core_xml_encode($body, $root, $item, $attr, $id, $encoding);
    }
}

if (!function_exists('jsonp')) {
    function jsonp($body, $callback = null)
    {
        if(is_null($callback)){
            $fallbackCallback = config("think.default_jsonp_handler")?:"jsonpReturn";
            $request = envar("request");
            if($request){
                $setting_var = config("think.jsonp_handler_setting_var");
                $setting_var = $setting_var?:"callback";
                $callback = $request->get($setting_var)?:$fallbackCallback;
                if(!think_core_jsonp_callback_name_check($callback)){
                    $callback = $fallbackCallback;
                }
            }else{
                $callback = $fallbackCallback;
            }
        }
        return $callback."(".json_encode($body).")";
    }
}


if (!function_exists('session')) {
    function session($name, $value = '')
    {
        if (is_null($name) || empty($name)) {
            Session::clear();
        } elseif ('' === $value) {
            return 0 === strpos($name, '?') ? Session::has(substr($name, 1)) : Session::get($name);
        } elseif (is_null($value)) {
            return Session::delete($name);
        } else {
            return Session::set($name, $value);
        }
    }
}


if (!function_exists('cache')) {
    /**
     * 缓存管理
     * @param mixed     $name 缓存名称，如果为数组表示进行缓存设置
     * @param mixed     $value 缓存值
     * @param mixed     $options 缓存参数
     * @param string    $tag 缓存标签
     * @return mixed
     */
    function cache($name, $value = '', $options = null, $tag = null)
    {
        if (is_array($options)) {
            // 缓存操作的同时初始化
            $cache = Cache::connect($options);
        } elseif (is_array($name)) {
            // 缓存初始化
            return Cache::connect($name);
        } else {
            $cache = Cache::init();
        }

        if (is_null($name)) {
            return $cache->clear($value);
        } elseif ('' === $value) {
            // 获取缓存
            return 0 === strpos($name, '?') ? $cache->has(substr($name, 1)) : $cache->get($name);
        } elseif (is_null($value)) {
            // 删除缓存
            return $cache->rm($name);
        } elseif (0 === strpos($name, '?') && '' !== $value) {
            $expire = is_numeric($options) ? $options : null;
            return $cache->remember(substr($name, 1), $value, $expire);
        } else {
            // 缓存数据
            if (is_array($options)) {
                $expire = isset($options['expire']) ? $options['expire'] : null; //修复查询缓存无法设置过期时间
            } else {
                $expire = is_numeric($options) ? $options : null; //默认快捷缓存设置过期时间
            }
            if (is_null($tag)) {
                return $cache->set($name, $value, $expire);
            } else {
                return $cache->tag($tag)->set($name, $value, $expire);
            }
        }
    }
}


if (!function_exists('lang')) {
    function lang($name, ...$vars){
        $lang = envar("lang");
        if($lang){
            return $lang->get($name, ...$vars);
        }
        return $name;
    }
}


if (!function_exists('envar')) {
    function envar($envname)
    {
        $envname = strtolower($envname);
        switch ($envname){
            case "req":
            case "request":
                global $TW_ENV_REQUEST;
                if(isset($TW_ENV_REQUEST)){
                    return $TW_ENV_REQUEST;
                }
                break;
            case "resp":
            case "response":
                global $TW_ENV_RESPONSE;
                if(isset($TW_ENV_RESPONSE)){
                    return $TW_ENV_RESPONSE;
                }
                break;
            case "lang":
                global $TW_ENV_LANG;
                if(isset($TW_ENV_LANG)){
                    return $TW_ENV_LANG;
                }else{
                    return new Lang();
                }
                break;
        }
        return null;
    }
}