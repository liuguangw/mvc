<?php
namespace liuguang\mvc;

use liuguang\mvc\services\IErrorHandler;
use liuguang\mvc\services\ErrorHandler;
use liuguang\mvc\services\IRouteErrorHandler;
use liuguang\mvc\services\RouteErrorHandler;
use liuguang\mvc\services\RouteHandler;
use liuguang\mvc\services\DefaultRouteHandler;
use liuguang\mvc\services\UrlAsset;
use liuguang\mvc\services\DefaultUrlAsset;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use liuguang\mvc\services\AbstractSession;
use liuguang\mvc\session\CacheSession;

/**
 * 容器服务加载
 *
 * @author liuguang
 *        
 */
class ServiceLoader
{

    /**
     * 加载容器服务
     *
     * @param Container $container            
     * @return void
     */
    public function loadContainerService(Container $container): void
    {
        // 添加错误处理服务
        $container->addClassMap(IErrorHandler::class, ErrorHandler::class, '@errorHandler');
        // 路由错误处理
        $container->addClassMap(IRouteErrorHandler::class, RouteErrorHandler::class, '@routeErrorHandler');
        // 路由服务
        $container->addClassMap(RouteHandler::class, DefaultRouteHandler::class, '@routeHandler');
        // 模板中静态url处理
        $container->addClassMap(UrlAsset::class, DefaultUrlAsset::class, '@urlAsset');
        // 缓存
        $container->addCallableMap(CacheInterface::class, function () {
            $cacheDirectory = PUBLIC_PATH . '/../src/cache';
            return new FilesystemCache('', 30 * 60, $cacheDirectory);
        }, '@cache');
        // session
        $container->addClassMap(AbstractSession::class, CacheSession::class, '@session');
    }
}