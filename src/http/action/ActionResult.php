<?php
namespace liuguang\mvc\http\action;

/**
 * 控制器响应接口
 * 
 * @author liuguang
 *
 */
interface ActionResult
{
    /**
     * 执行响应结果
     * 
     * @return void
     */
    public function executeResult(): void;
}

