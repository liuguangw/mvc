<?php
namespace liuguang\mvc\event\common;

use liuguang\mvc\event\EventArgs;

class ApplicationErrorEvent extends EventArgs
{

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
     * @return \liuguang\mvc\event\common\ApplicationErrorEvent
     */
    public static function createCustom(int $code, string $message)
    {
        return new static(new \Exception($message, $code));
    }
}