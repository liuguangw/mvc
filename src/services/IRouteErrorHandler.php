<?php
namespace liuguang\mvc\services;

use liuguang\mvc\event\common\RouteErrorEvent;

interface IRouteErrorHandler
{

    /**
     * 处理错误事件
     *
     * @param ApplicationErrorEvent $evt 路由错误事件
     * @return void         
     */
    public function handleError(RouteErrorEvent $evt): void;
}

