<?php
namespace liuguang\mvc\event\common;

/**
 * 路由错误事件
 * 
 * @author liuguang
 *
 */
class RouteErrorEvent extends ApplicationErrorEvent
{

    public $httpErrorCode;

    public function __construct(\Throwable $errorInfo, int $httpErrorCode = 500)
    {
        $this->errorInfo = $errorInfo;
        $this->httpErrorCode = $httpErrorCode;
    }
}

