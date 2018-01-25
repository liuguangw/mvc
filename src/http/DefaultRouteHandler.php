<?php
namespace liuguang\mvc\http;

use liuguang\mvc\data\DataMap;

/**
 * 默认的路由
 *
 * @author liuguang
 *        
 */
class DefaultRouteHandler implements RouteHandler
{

    private $defaultController;

    private $defaultAction;

    private $controllerKey;

    private $actionKey;

    public function __construct()
    {
        $this->defaultController = 'home.Index';
        $this->defaultAction = 'index';
        $this->controllerKey = 'c';
        $this->actionKey = 'a';
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\http\RouteHandler::getRouteInfo()
     */
    public function getRouteInfo(string $url): RouteInfo
    {
        $params = new DataMap($_GET);
        $controllerName = $params->getValue($this->controllerKey, $this->defaultController);
        $actionName = $params->getValue($this->actionKey, $this->defaultAction);
        if ($params->containsKey($this->controllerKey)) {
            $params->remove($this->controllerKey);
        }
        if ($params->containsKey($this->actionKey)) {
            $params->remove($this->actionKey);
        }
        return new RouteInfo($controllerName, $actionName, $params);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\http\RouteHandler::createUrl()
     */
    public function createUrl(string $controllerName, string $actionName, ?DataMap $params = null): string
    {
        $data = [
            $this->controllerKey => $controllerName,
            $this->actionKey => $actionName
        ];
        if ($params != null) {
            if ($params->containsKey($this->controllerKey)) {
                $params->remove($this->controllerKey);
            }
            if ($params->containsKey($this->actionKey)) {
                $params->remove($this->actionKey);
            }
            $data = array_merge($data, $params->toArray());
        }
        return '/?' . http_build_query($data);
    }
}

