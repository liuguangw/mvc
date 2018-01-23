<?php
namespace liuguang\mvc\http;

use liuguang\mvc\http\action\ActionResult;
use liuguang\mvc\Application;
use liuguang\mvc\event\common\RouteErrorEvent;
use liuguang\mvc\data\DataMap;
use liuguang\mvc\http\action\ViewResult;

/**
 * 控制器基类
 *
 * @author liuguang
 *        
 */
class Controller
{

    protected $currentControllerName;

    protected $currentActionName;

    protected function callAction(RouteInfo $routeInfo): ActionResult
    {
        $this->currentControllerName = $routeInfo->getControllerName();
        $this->currentActionName = $routeInfo->getActionName();
        $app = Application::$app;
        $actionMethodPrefix = $app->config->getValue('ACTION_METHOD_PREFIX');
        $methodName = '';
        if (empty($actionMethodPrefix)) {
            $methodName = $this->currentActionName;
        } else {
            $methodName = $actionMethodPrefix . ucfirst($this->currentActionName);
        }
        $methods = get_class_methods(get_class($this));
        if (in_array($methodName, $methods)) {
            return call_user_func([
                $this,
                $methodName
            ], $routeInfo);
        } else {
            $event = RouteErrorEvent::createCustom(404, $this->currentControllerName . '/' . $this->currentActionName . '对应的方法' . $methodName . '不存在');
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

    /**
     * 以视图响应
     *
     * @param DataMap $params
     *            模板变量
     * @param string $viewName
     *            视图名称
     * @return ViewResult
     */
    protected function view(DataMap $params, ?string $viewName = null): ViewResult
    {
        if ($viewName == null) {
            $viewName = str_replace('.', '/', $this->currentControllerName) . '/' . $this->currentActionName;
        }
        return new ViewResult($viewName, $params);
    }
}