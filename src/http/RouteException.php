<?php
namespace liuguang\mvc\http;

/**
 * 路由异常
 *
 * @author liuguang
 *        
 */
class RouteException extends \Exception
{

    /**
     * http错误状态码
     *
     * @var int
     */
    public $httpCode;

    public function __construct(string $message, int $httpCode = 500)
    {
        $this->httpCode = $httpCode;
        parent::__construct($message, $httpCode);
    }
}

