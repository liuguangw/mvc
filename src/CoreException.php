<?php
namespace liuguang\mvc;

class CoreException extends \Exception
{

    // 异常类型定义
    const FILE_NOT_FOUND = 1000;

    // 异常附加数据
    private $extraData;

    public function __construct(int $code, string $message, $extraData = null)
    {
        parent::__construct($message, $code);
        $this->extraData = $extraData;
    }

    /**
     * 异常附加信息
     *
     * @return mixed
     */
    public function getExtraData()
    {
        return $this->extraData;
    }
}