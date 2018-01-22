<?php
namespace liuguang\mvc\event\common;

use liuguang\mvc\event\EventArgs;

class ApplicationErrorEvent extends EventArgs
{

    /**
     * 错误信息
     * 
     * @var \Throwable
     */
    public $errorInfo;

    public function __construct(\Throwable $errorInfo)
    {
        $this->errorInfo = $errorInfo;
    }

    /**
     * 创建一个自定义的错误事件
     * 
     * @param int $code
     * @param string $message
     * @return static
     */
    public static function createCustom(int $code, string $message)
    {
        return new static(new \Exception($message, $code));
    }
}