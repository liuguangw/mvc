<?php
namespace liuguang\mvc\http;

interface RouteHandler
{
    /**
     * 解析http请求获取路由信息
     *
     * @param string url部分
     * @return RouteInfo
     */
    public function getRouteInfo(string $url): RouteInfo;

    /**
     * 根据路由信息生成URL地址
     *
     * @param RouteInfo $routeInfo
     *            路由信息
     * @return string
     */
    public function createUrl(RouteInfo $routeInfo): string;
}

