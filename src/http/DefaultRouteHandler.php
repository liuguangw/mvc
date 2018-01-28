<?php
namespace liuguang\mvc\http;

use liuguang\mvc\data\DataMap;
use liuguang\mvc\Application;
use liuguang\mvc\event\common\RouteErrorEvent;

/**
 * 默认的路由
 *
 * @author liuguang
 *        
 */
class DefaultRouteHandler extends RouteHandler
{

    private $routeKey;

    public function __construct()
    {
        $this->routeKey = 'r';
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
        $route = '';
        if ($params->containsKey($this->routeKey)) {
            $route = $params->getValue($this->routeKey, '');
            $params->remove($this->routeKey);
        }
        $routeInfo = $this->parseRoute($route);
        $routeInfo->params = $params;
        // 验证路由是否合法
        if (! $this->isModuleName($routeInfo->moduleName)) {
            $event = RouteErrorEvent::createCustom(400, '模块名' . $routeInfo->moduleName . '非法');
            $event->httpErrorCode = 400;
            Application::$app->dispatchEvent($event);
        }
        if (! $this->isControllerName($routeInfo->controllerName)) {
            $event = RouteErrorEvent::createCustom(400, '控制器名' . $routeInfo->controllerName . '非法');
            $event->httpErrorCode = 400;
            Application::$app->dispatchEvent($event);
        }
        if (! $this->isActionName($routeInfo->actionName)) {
            $event = RouteErrorEvent::createCustom(400, '操作名' . $routeInfo->actionName . '非法');
            $event->httpErrorCode = 400;
            Application::$app->dispatchEvent($event);
        }
        return $routeInfo;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\http\RouteHandler::createUrl()
     */
    public function createUrl(string $moduleName, string $controllerName, string $actionName, ?DataMap $params = null): string
    {
        // 检验名称
        $url = parent::createUrl($moduleName, $controllerName, $actionName);
        $routeArr = [
            $moduleName,
            $controllerName,
            $actionName
        ];
        // 省略路由中的默认部分
        if ($actionName == $this->defaultAction) {
            array_pop($routeArr);
            if ($controllerName == $this->defaultController) {
                array_pop($routeArr);
                if ($moduleName == $this->defaultModule) {
                    $routeArr = [];
                }
            }
        }
        // 获取参数数组
        $paramsArray = [];
        if ($params !== null) {
            if ($params->containsKey($this->routeKey)) {
                $params->remove($this->routeKey);
            }
            $paramsArray = $params->toArray();
        }
        // 合并
        if (! empty($routeArr)) {
            $paramsArray = array_merge([
                $this->routeKey => implode('/', $routeArr)
            ], $paramsArray);
        }
        if (! empty($paramsArray)) {
            $url .= ('?' . http_build_query($paramsArray));
        }
        return $url;
    }
}

