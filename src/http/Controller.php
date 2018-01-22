<?php
namespace liuguang\mvc\http;

use liuguang\mvc\http\action\ActionResult;
use liuguang\mvc\Application;
use liuguang\mvc\event\common\RouteErrorEvent;

/**
 * 控制器基类
 *
 * @author liuguang
 *        
 */
class Controller
{

    protected function callAction(RouteInfo $routeInfo): ActionResult
    {
        $actionName = $routeInfo->getActionName();
        $app = Application::$app;
        $actionMethodPrefix = $app->config->getValue('ACTION_METHOD_PREFIX');
        $methodName = '';
        if (empty($actionMethodPrefix)) {
            $methodName = $routeInfo->getActionName();
        } else {
            $methodName = $actionMethodPrefix . ucfirst($routeInfo->getActionName());
        }
        $methods = get_class_methods(get_class($this));
        if (in_array($methodName, $methods)) {
            return call_user_func([
                $this,
                $methodName
            ], $routeInfo);
        } else {
            $event = RouteErrorEvent::createCustom(404, $routeInfo->getControllerName() . '/' . $routeInfo->getActionName() . '对应的方法' . $methodName . '不存在');
            $event->httpErrorCode = 404;
            $app->dispatchEvent($event);
        }
    }

    /**
     * 执行操作之前的操作
     *
     * @param RouteInfo $routeInfo
     *            路由信息
     * @return ActionResult 响应结果
     */
    public function beforeAction(RouteInfo $routeInfo): ActionResult
    {
        return $this->callAction($routeInfo);
    }
}