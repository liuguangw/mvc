<?php
namespace liuguang\mvc\http;

use liuguang\mvc\data\DataMap;

interface RouteHandler
{

    /**
     * 解析http请求获取路由信息
     *
     * @param
     *            string url部分
     * @return RouteInfo
     */
    public function getRouteInfo(string $url): RouteInfo;

    /**
     * 根据路由信息生成URL地址
     *
     * @param string $controllerName
     *            控制器名
     * @param string $actionName
     *            操作名
     * @param DataMap $params
     *            路由参数
     * @return string
     */
    public function createUrl(string $controllerName, string $actionName, ?DataMap $params = null): string;
}

