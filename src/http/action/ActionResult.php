<?php
namespace liuguang\mvc\http\action;

use liuguang\mvc\Application;

/**
 * 控制器响应抽象类
 *
 * @author liuguang
 *        
 */
abstract class ActionResult
{

    /**
     * 状态码
     *
     * @var integer
     */
    public $statusCode = 200;

    /**
     * 文档类型
     *
     * @var string
     */
    public $contentType = 'text/html; charset=utf-8';

    /**
     * 额外的HTTP头
     *
     * @var array
     */
    public $extraHeaders = [];

    /**
     * 初始化额外的HTTP头
     *
     * @return void
     */
    protected function initExtraHeaders(): void
    {
        $this->extraHeaders = Application::$app->config->getValue('DEFAULT_EXTRA_HEADERS');
    }

    /**
     * 输出内容
     *
     * @return void
     */
    protected abstract function outputContent(): void;

    /**
     * 执行响应结果
     *
     * @return void
     */
    public function executeResult(): void
    {
        // 状态码
        http_response_code($this->statusCode);
        // Content-Type
        if ($this->contentType != '') {
            header('Content-Type: ' . $this->contentType);
        }
        // 额外HTTP头
        if (! empty($this->extraHeaders)) {
            foreach ($this->extraHeaders as $value) {
                header($value);
            }
        }
        // session提交
        if (Application::$app->sessionStarted) {
            Application::$app->getService('session')->commit();
        }
        // 输出
        $this->outputContent();
    }
}

