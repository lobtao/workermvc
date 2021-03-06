<?php
/**
 * Created by lobtao.
 */

namespace workermvc\exception;



class ControllerException extends HttpException
{
    /**
     * @var string
     */
    protected $controller, $method;

    /**
     * ControllerException constructor.
     *
     * @param \Exception $origin
     * @param string $controller
     * @param bool $method
     * @param string $message
     */
    public function __construct($origin, $controller, $method, $message = "")
    {
        if(!is_null($origin) && is_object($origin)){
            $message = $origin->getMessage();
        }
        parent::__construct(500, $message, true, $origin);
        $this->controller = $controller;
        $this->method = $method;
        if(config("think.debug")==true) {
            $this->setHttpBody($this->getDebugHttpBody());
        }else{
            $this->setHttpBody($this->getProHttpBody());
        }
    }

    /**
     * Get Http Return in Debug Mode
     *
     * @return string
     */
    private function getDebugHttpBody(){
        return $this->loadTemplate("TracingPage", [
            'title' => think_core_lang("tracing page controller process error"),
            'main_msg' => think_core_lang("tracing page controller process error"),
            'main_msg_detail' => $this->controller.(is_null($this->method)?"":("->".$this->method."()")),
            'main_error_pos' => $this->formErrorPos(),
            'main_error_detail' => $this->formErrorMsg(),
            'lang_tracing' => think_core_lang("tracing page tracing"),
            'lang_src' => think_core_lang("tracing page src file"),
            'lang_line' => think_core_lang('tracing page line num'),
            'lang_call' => think_core_lang("tracing page call"),
            'tracing_table' => $this->formTracingTable(),
            'request_table' => $this->formRequestTable(),
            'env_table' => $this->formEnvTable(),
            'lang_key' => think_core_lang("tracing page key"),
            'lang_value' => think_core_lang("tracing page value"),
            'lang_request' => think_core_lang("tracing page request detail"),
            'lang_env' => think_core_lang("tracing page env")
        ]);
    }

    /**
     * Get Http Return in Production Mode
     *
     * @return string
     */
    private function getProHttpBody(){
        return $this->loadTemplate("ErrorPage", [
            'title'=>think_core_lang('page error title'),
            'code'=>500,
            'msg'=>think_core_lang('page error msg')
        ]);
    }
}