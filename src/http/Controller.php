<?php
namespace liuguang\mvc\http;

use liuguang\mvc\Application;
use liuguang\mvc\data\DataMap;
use liuguang\mvc\http\action\ViewResult;
use liuguang\mvc\http\action\ActionResult;
use liuguang\mvc\event\common\RouteErrorEvent;
use liuguang\mvc\http\action\ContentResult;

/**
 * 控制器基类
 *
 * @author liuguang
 *        
 */
abstract class Controller
{

    /**
     * 布局名
     *
     * @var string
     */
    protected $layout = 'main';

    /**
     * 执行操作
     *
     * @param string $actionName
     *            操作名
     * @param string $actionMethodName
     *            方法名
     * @param bool $actionExists
     *            操作是否存在
     * @return ActionResult
     */
    public function invokeAction(string $actionName, string $actionMethodName, bool $actionExists): ActionResult
    {
        if (! $actionExists) {
            $event = RouteErrorEvent::createCustom(404, get_class($this) . '中的操作方法' . $actionMethodName . '不存在');
            $event->httpErrorCode = 404;
            Application::$app->dispatchEvent($event);
            return new ContentResult('');
        }
        $actionResult = call_user_func([
            $this,
            $actionMethodName
        ]);
        return $actionResult;
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
            $routeInfo = Application::$app->routeInfo;
            $viewName = $routeInfo->controllerName . '/' . $routeInfo->actionName;
        }
        return new ViewResult($viewName, $this->layout, $params);
    }
}