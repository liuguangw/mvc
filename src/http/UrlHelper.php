<?php
namespace liuguang\mvc\http;

use liuguang\mvc\data\DataMap;

class UrlHelper
{

    private $routeHandler;

    private $context;

    public function __construct(RouteHandler $routeHandler, string $context)
    {
        $this->routeHandler = $routeHandler;
        $this->context = $context;
    }

    /**
     * url生成
     * 
     * @param string $controllerName 控制器名
     * @param string $actionName 操作名
     * @param DataMap $params 路由参数
     * @param bool $isFullUrl 是否返回完整url
     * @param array $fullUrlInfo 完整url特别指定信息
     * @return string
     */
    public function createUrl(string $controllerName, string $actionName, ?DataMap $params=null, bool $isFullUrl = false, array $fullUrlInfo = []): string
    {
        $resourcePath = $this->routeHandler->createUrl($controllerName, $actionName, $params);
        return $this->getPublicUrl($resourcePath, $isFullUrl, $fullUrlInfo);
    }

    /**
     * 获取公共URL
     *
     * @param string $resourcePath
     *            资源路径
     * @param bool $isFullUrl
     *            是否返回完整路径
     * @param array $fullUrlInfo
     *            完整路径附加信息
     */
    public function getPublicUrl(string $resourcePath = '/', bool $isFullUrl = false, array $fullUrlInfo = [])
    {
        $url = $this->context . $resourcePath;
        if ($isFullUrl) {
            $this->formatUrlData($fullUrlInfo);
            $prefix = $fullUrlInfo['scheme'] . '://' . $fullUrlInfo['host'];
            if (($fullUrlInfo['scheme'] == 'http') && ($fullUrlInfo['port'] != 80)) {
                $prefix .= (':' . $fullUrlInfo['port']);
            } elseif (($fullUrlInfo['scheme'] == 'https') && ($fullUrlInfo['port'] != 443)) {
                $prefix .= (':' . $fullUrlInfo['port']);
            }
            $url = $prefix . $url;
        }
        return $url;
    }

    /**
     * 格式化URL各部分
     *
     * @param array $fullUrlInfo            
     * @return void
     */
    private function formatUrlData(array &$fullUrlInfo): void
    {
        if (! isset($fullUrlInfo['scheme'])) {
            if (! isset($_SERVER['HTTPS'])) {
                $fullUrlInfo['scheme'] = 'http';
            } elseif ($_SERVER['HTTPS'] == 'off') {
                $fullUrlInfo['scheme'] = 'http';
            } else {
                $fullUrlInfo['scheme'] = 'https';
            }
        }
        if (! isset($fullUrlInfo['host'])) {
            $fullUrlInfo['host'] = 'localhost';
            if (isset($_SERVER['HTTP_HOST'])) {
                $fullUrlInfo['host'] = $_SERVER['HTTP_HOST'];
            }
        }
        if (! isset($fullUrlInfo['port'])) {
            $fullUrlInfo['port'] = 80;
            if (isset($_SERVER['SERVER_PORT'])) {
                $fullUrlInfo['port'] = $_SERVER['SERVER_PORT'];
            }
        }
    }
}

