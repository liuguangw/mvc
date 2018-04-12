<?php
namespace liuguang\mvc;

use liuguang\mvc\data\DataMap;
use liuguang\mvc\event\EventDispatcher;
use liuguang\mvc\event\common\ApplicationErrorEvent;
use liuguang\mvc\http\UrlHelper;
use liuguang\mvc\event\common\RouteErrorEvent;
use liuguang\mvc\http\Controller;
use liuguang\mvc\http\action\ActionResult;
use liuguang\mvc\services\IErrorHandler;
use liuguang\mvc\services\IRouteErrorHandler;
use liuguang\mvc\services\RouteHandler;
use liuguang\mvc\http\RouteException;

/**
 * 应用主类
 *
 * @author liuguang
 *        
 */
class Application
{
    use EventDispatcher;

    /**
     * 应用实例
     *
     * @var Application
     */
    public static $app = null;

    /**
     * web上下文路径
     *
     * @var string
     */
    public $appContext;

    /**
     * mvc源代码(src)目录
     *
     * @var string
     */
    public $mvcSourcePath;

    /**
     * 应用配置对象
     *
     * @var DataMap
     */
    public $config = null;

    /**
     * URL处理工具
     *
     * @var \liuguang\mvc\http\UrlHelper
     */
    public $url;

    /**
     * 路由信息
     *
     * @var \liuguang\mvc\http\RouteInfo
     */
    public $routeInfo;

    /**
     * Ioc容器
     *
     * @var Container
     */
    public $container;

    public $sessionStarted = false;

    public function __construct()
    {
        $this->mvcSourcePath = __DIR__;
        if (! defined('PUBLIC_PATH')) {
            exit('PUBLIC_PATH is not defined !');
        }
        if (! defined('APP_CONFIG_PATH')) {
            define('APP_CONFIG_PATH', PUBLIC_PATH . '/../src/config');
        }
        $context = '';
        $pos = strrpos($_SERVER['SCRIPT_NAME'], '/');
        if ($pos > 0) {
            $context = substr($_SERVER['SCRIPT_NAME'], 0, $pos);
        }
        $this->appContext = $context;
    }

    /**
     * 启动基本组件
     *
     * @return void
     */
    private function startCommon(): void
    {
        if (self::$app !== null) {
            return;
        }
        self::$app = $this;
        // 加载框架配置文件
        $config = DataMap::loadFromPhpFile($this->mvcSourcePath . '/../config.inc.php');
        // 应用配置
        if ($this->config === null) {
            $appConfigFile = APP_CONFIG_PATH . '/./config.inc.php';
            if (is_file($appConfigFile)) {
                $appConfig = DataMap::loadFromPhpFile($appConfigFile);
                $config->mergeData($appConfig);
            }
        } else {
            $config->mergeData($this->config);
        }
        $this->config = $config;
        $this->loadContainer();
    }

    /**
     * 启动应用
     *
     * @return void
     */
    public function startApp(): void
    {
        $this->startCommon();
        $this->invokeRoute();
    }

    /**
     * 测试入口
     *
     * @return void
     */
    public function startTest(): void
    {
        $this->startCommon();
    }

    /**
     * 服务对象获取
     *
     * @param string $serviceName
     *            服务名称
     * @return object
     */
    public function getService(string $serviceName)
    {
        return $this->container->make('@' . $serviceName);
    }

    /**
     * 加载容器
     *
     * @return void
     */
    private function loadContainer(): void
    {
        $loaderClass = $this->config->getValue('SERVICE_LOADER');
        $this->container = new Container();
        $serviceLoader = new $loaderClass();
        $serviceLoader->loadContainerService($this->container);
        // 错误处理器
        $this->loadErrorHandler($this->getService('errorHandler'));
        $this->loadRouteErrorHandler($this->getService('routeErrorHandler'));
        // 路由服务
        $this->loadRouteHandler($this->getService('routeHandler'));
    }

    /**
     * 加载错误处理器
     *
     * @param IErrorHandler $errorHandler
     *            错误处理器
     * @return void
     */
    private function loadErrorHandler(IErrorHandler $errorHandler): void
    {
        // 添加错误事件处理
        $this->addEventListener(ApplicationErrorEvent::class, [
            $errorHandler,
            'handleError'
        ]);
        // 当发生错误或者异常时,发送事件
        set_exception_handler(function ($exception) {
            if ($exception instanceof RouteException) {
                $this->dispatchEvent(new RouteErrorEvent($exception, $exception->httpCode));
            } else {
                $this->dispatchEvent(new ApplicationErrorEvent($exception));
            }
        });
        set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) {
            $this->dispatchEvent(ApplicationErrorEvent::createCustom($errno, $errstr));
        });
    }

    /**
     * 加载路由错误处理接口
     *
     * @param IRouteErrorHandler $errorHandler            
     * @return void
     */
    private function loadRouteErrorHandler(IRouteErrorHandler $errorHandler): void
    {
        // 添加路由错误事件处理
        $this->addEventListener(RouteErrorEvent::class, [
            $errorHandler,
            'handleError'
        ]);
    }

    /**
     * 加载路由
     *
     * @return void
     */
    private function loadRouteHandler(RouteHandler $routeHandler): void
    {
        // 解析URL
        $url = '/';
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = $_SERVER['REQUEST_URI'];
        }
        $this->routeInfo = $routeHandler->getRouteInfo($url);
        $this->url = new UrlHelper($routeHandler, $this->appContext);
    }

    /**
     * 执行路由
     *
     * @return void
     */
    private function invokeRoute(): void
    {
        $moduleName = $this->routeInfo->moduleName;
        $controllerName = $this->routeInfo->controllerName;
        $actionName = $this->routeInfo->actionName;
        $controllerClass = $this->config->getValue('CONTROLLER_NAMESPACE') . '\\' . str_replace('.', '\\', $moduleName) . '\\' . $controllerName;
        if (! class_exists($controllerClass)) {
            $event = RouteErrorEvent::createCustom(404, $moduleName . '/' . $controllerName . '/' . $actionName . '对应的控制器类' . $controllerClass . '不存在');
            $event->httpErrorCode = 404;
            $this->dispatchEvent($event);
            return;
        }
        // 执行操作
        $this->invokeAction(new $controllerClass(), $actionName);
    }

    /**
     * 执行操作
     *
     * @param Controller $controller            
     * @param string $actionName            
     */
    private function invokeAction(Controller $controller, string $actionName): void
    {
        // 获取操作结果
        $actionMethodName = $actionName;
        $actionMethodPrefix = $this->config->getValue('ACTION_METHOD_PREFIX');
        if (! empty($actionMethodPrefix)) {
            $actionMethodName = $actionMethodPrefix . ucfirst($actionMethodName);
        }
        $methods = get_class_methods($controller);
        $actionExists = in_array($actionMethodName, $methods);
        if (! $actionExists) {
            throw new RouteException(get_class($controller) . '中的操作方法' . $actionMethodName . '不存在', 404);
        }
        $actionResult = $controller->invokeAction($actionName, $actionMethodName);
        $this->invokeActionResult($actionResult);
    }

    /**
     * 调用操作
     *
     * @param string $action
     *            缺省操作
     * @param DataMap $params
     *            路由参数(当为空时,使用当前的路由参数)
     * @return void
     */
    public function callAction(string $action, ?DataMap $params = null): void
    {
        list ($moduleName, $controllerName, $actionName) = $this->getFullAction($action);
        $this->routeInfo->moduleName = $moduleName;
        $this->routeInfo->controllerName = $controllerName;
        $this->routeInfo->actionName = $actionName;
        if ($params != null) {
            $this->routeInfo->params = $params;
        }
        $this->invokeRoute();
    }

    /**
     * 执行响应结果
     *
     * @param ActionResult $actionResult            
     * @return void
     */
    private function invokeActionResult(ActionResult $actionResult): void
    {
        $actionResult->executeResult();
    }

    /**
     * 获取完整的module/controller/action
     *
     * @param string $action            
     * @return array
     */
    public function getFullAction(string $action = ''): array
    {
        // 初始化默认值
        $moduleName = $this->routeInfo->moduleName;
        $controllerName = $this->routeInfo->controllerName;
        $actionName = $this->routeInfo->actionName;
        // 分割
        if ($action != '') {
            $arr = explode('/', $action);
            $arrLength = count($arr);
            if ($arrLength == 1) {
                list ($actionName) = $arr;
            } elseif ($arrLength == 2) {
                list ($controllerName, $actionName) = $arr;
            } elseif ($arrLength == 3) {
                list ($moduleName, $controllerName, $actionName) = $arr;
            }
        }
        return [
            $moduleName,
            $controllerName,
            $actionName
        ];
    }
}

