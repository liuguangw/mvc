<?php
namespace liuguang\mvc\http;

use liuguang\mvc\data\DataMap;
use liuguang\mvc\Application;
use liuguang\mvc\event\common\ApplicationErrorEvent;

/**
 * 路由抽象类
 *
 * @author liuguang
 *        
 */
abstract class RouteHandler
{

    /**
     * 模块名正则
     *
     * @var string
     */
    protected $moduleNamePattern = '([a-z_][a-z0-9_]*\.)*[a-z_][a-z0-9_]*';

    /**
     * 控制器名正则
     *
     * @var string
     */
    protected $controllerNamePattern = '[A-Z_][a-zA-Z0-9_]*';

    /**
     * 操作名正则
     *
     * @var string
     */
    protected $actionNamePattern = '[a-z_][a-zA-Z0-9_]*';

    /**
     * 默认模块名
     *
     * @var string
     */
    protected $defaultModule = 'home';

    /**
     * 默认控制器名
     *
     * @var string
     */
    protected $defaultController = 'Index';

    /**
     * 默认操作名
     *
     * @var string
     */
    protected $defaultAction = 'index';

    /**
     * 解析http请求获取路由信息
     *
     * @param
     *            string url部分
     * @return RouteInfo
     */
    public abstract function getRouteInfo(string $url): RouteInfo;

    /**
     * 根据路由信息生成URL地址
     *
     * @param string $moduleName
     *            模块名
     * @param string $controllerName
     *            控制器名
     * @param string $actionName
     *            操作名
     * @param DataMap $params
     *            路由参数
     * @return string
     */
    public function createUrl(string $moduleName, string $controllerName, string $actionName, ?DataMap $params = null): string
    {
        $url = '/';
        if (! $this->isModuleName($moduleName)) {
            Application::$app->dispatchEvent(ApplicationErrorEvent::createCustom(500, '模块名' . $moduleName . '非法'));
        }
        if (! $this->isControllerName($controllerName)) {
            Application::$app->dispatchEvent(ApplicationErrorEvent::createCustom(500, '控制器名' . $controllerName . '非法'));
        }
        if (! $this->isActionName($actionName)) {
            Application::$app->dispatchEvent(ApplicationErrorEvent::createCustom(500, '操作名' . $actionName . '非法'));
        }
        return $url;
    }

    public function parseRoute(string $route): RouteInfo
    {
        // 初始化默认值
        $moduleName = $this->defaultModule;
        $controllerName = $this->defaultController;
        $actionName = $this->defaultAction;
        // 分割
        if ($route != '') {
            $arr = explode('/', $route);
            $arrLength = count($arr);
            if ($arrLength == 1) {
                list ($moduleName) = $arr;
            } elseif ($arrLength == 2) {
                list ($moduleName, $controllerName) = $arr;
            } elseif ($arrLength == 3) {
                list ($moduleName, $controllerName, $actionName) = $arr;
            }
        }
        return new RouteInfo($moduleName, $controllerName, $actionName);
    }

    /**
     * 正则判断模块名的合法性
     *
     * @param string $moduleName
     *            模块名
     * @return bool
     */
    public function isModuleName(string $moduleName): bool
    {
        return (preg_match('/^' . $this->moduleNamePattern . '$/', $moduleName) != 0);
    }

    /**
     * 正则判断控制器名的合法性
     *
     * @param string $controllerName
     *            控制器名
     * @return bool
     */
    public function isControllerName(string $controllerName): bool
    {
        return (preg_match('/^' . $this->controllerNamePattern . '$/', $controllerName) != 0);
    }

    /**
     * 正则判断操作名的合法性
     *
     * @param string $actionName
     *            操作名
     * @return bool
     */
    public function isActionName(string $actionName): bool
    {
        return (preg_match('/^' . $this->actionNamePattern . '$/', $actionName) != 0);
    }
}

