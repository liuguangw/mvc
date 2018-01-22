<?php
namespace liuguang\mvc\http\action;

use liuguang\mvc\Application;

/**
 * 跳转路由页面
 *
 * @author liuguang
 *        
 */
class RedirectToRouteResult implements ActionResult
{

    /**
     * 是否为永久性转移
     *
     * @var bool
     */
    public $permanent = false;

    /**
     * 路由信息
     *
     * @var \liuguang\mvc\http\RouteInfo
     */
    public $routeInfo;

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\http\action\ActionResult::executeResult()
     */
    public function executeResult(): void
    {
        $result = new RedirectResult();
        $result->permanent = $this->permanent;
        $result->url = Application::$app->url->createUrl($this->routeInfo);
        $result->executeResult();
    }
}

