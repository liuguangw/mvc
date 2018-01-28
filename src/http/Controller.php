<?php
namespace liuguang\mvc\http;

use liuguang\mvc\Application;
use liuguang\mvc\data\DataMap;
use liuguang\mvc\http\action\ViewResult;

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
     * 执行操作之前的操作
     *
     * @return void
     */
    public function beforeAction(string $actionName): void
    {}

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