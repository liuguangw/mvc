<?php
namespace liuguang\mvc\http\action;

use liuguang\mvc\Application;
use liuguang\mvc\http\RouteInfo;

/**
 * 跳转路由页面
 *
 * @author liuguang
 *        
 */
class RedirectToRouteResult extends ActionResult
{

    /**
     * 路由信息
     *
     * @var \liuguang\mvc\http\RouteInfo
     */
    public $routeInfo;

    public function __construct(RouteInfo $routeInfo, bool $permanent = false)
    {
        $this->routeInfo = $routeInfo;
        $this->statusCode = $permanent ? 301 : 302;
        $this->contentType = '';
        $this->initExtraHeaders();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\http\action\ActionResult::outputContent()
     */
    protected function outputContent(): void
    {
        $controllerName = $this->routeInfo->getControllerName();
        $actionName = $this->routeInfo->getActionName();
        $params = $this->routeInfo->getParams();
        header('Location: ' . Application::$app->url->createUrl($controllerName, $actionName, $params));
    }
}

