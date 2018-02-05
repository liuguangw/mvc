<?php
namespace liuguang\mvc\services;

use liuguang\mvc\event\common\ApplicationErrorEvent;

interface IErrorHandler
{

    /**
     * 处理错误事件
     *
     * @param ApplicationErrorEvent $evt 错误事件
     * @return void         
     */
    public function handleError(ApplicationErrorEvent $evt): void;
}

