<?php
namespace liuguang\mvc\http;

use liuguang\mvc\data\DataMap;

/**
 * 路由信息
 *
 * @author liuguang
 *        
 */
class RouteInfo
{

    /**
     * 模块名
     *
     * @var string
     */
    public $moduleName;

    /**
     * 控制器名
     *
     * @var string
     */
    public $controllerName;

    /**
     * 操作名
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
     * 构造方法
     *
     * @param string $moduleName
     *            模块名
     * @param string $controllerName
     *            控制器名
     * @param string $actionName
     *            操作名
     * @param DataMap $params
     *            路由参数
     */
    public function __construct(string $moduleName, string $controllerName, string $actionName, ?DataMap $params = null)
    {
        $this->moduleName = $moduleName;
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        if ($params === null) {
            $this->params = DataMap::getNewInstance();
        } else {
            $this->params = $params;
        }
    }
}

