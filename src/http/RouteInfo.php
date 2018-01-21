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

    private $controllerName;

    private $actionName;

    private $params;

    /**
     *
     * @param string $controllerName            
     * @param string $actionName            
     * @param DataMap $params            
     */
    public function __construct(string $controllerName, string $actionName, DataMap $params)
    {
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        $this->params = $params;
    }

    /**
     * 获取控制器名称
     *
     * @return string
     */
    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    /**
     * 获取操作名称
     *
     * @return string
     */
    public function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * 获取路由参数
     *
     * @return string
     */
    public function getParams(): DataMap
    {
        return $this->params;
    }
}

