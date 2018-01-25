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

    /**
     * 控制器名称
     *
     * @var string
     */
    public $controllerName;

    /**
     * 操作名称
     *
     * @var string
     */
    public $actionName;

    /**
     * 路由参数
     *
     * @var DataMap
     */
    public $params;

    /**
     * 布局名
     *
     * @var string
     */
    protected $layout = 'main';

    public function setRouteInfo(RouteInfo $routeInfo)
    {
        $this->controllerName = $routeInfo->getControllerName();
        $this->actionName = $routeInfo->getActionName();
        $this->params = $routeInfo->getParams();
    }

    /**
     * 执行操作之前的操作
     *
     * @return ActionResult 响应结果
     */
    public function beforeAction(): ActionResult
    {
        $app = Application::$app;
        $actionMethodPrefix = $app->config->getValue('ACTION_METHOD_PREFIX');
        $methodName = '';
        if (empty($actionMethodPrefix)) {
            $methodName = $this->actionName;
        } else {
            $methodName = $actionMethodPrefix . ucfirst($this->actionName);
        }
        $methods = get_class_methods(get_class($this));
        if (in_array($methodName, $methods)) {
            return call_user_func([
                $this,
                $methodName
            ]);
        } else {
            $event = RouteErrorEvent::createCustom(404, $this->controllerName . '/' . $this->actionName . '对应的方法' . $methodName . '不存在');
            $event->httpErrorCode = 404;
            $app->dispatchEvent($event);
        }
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
            $viewName = str_replace('.', '/', $this->controllerName) . '/' . $this->actionName;
        }
        return new ViewResult($viewName, $this->layout, $params);
    }
}