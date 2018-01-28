<?php
namespace liuguang\mvc;

use liuguang\mvc\data\DataMap;
use liuguang\mvc\event\EventDispatcher;
use liuguang\mvc\event\common\ApplicationErrorEvent;
use liuguang\mvc\http\UrlHelper;
use liuguang\mvc\http\RouteInfo;
use liuguang\mvc\event\common\RouteErrorEvent;
use liuguang\mvc\http\Controller;
use liuguang\mvc\http\action\ActionResult;
use liuguang\mvc\http\RouteHandler;

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

    public function __construct()
    {
        $this->mvcSourcePath = __DIR__;
        if (! defined('APP_PATH')) {
            exit('APP_PATH is not defined !');
        }
        if (! defined('APP_CONFIG_PATH')) {
            define('APP_CONFIG_PATH', APP_PATH . '/../config');
        }
        if (! defined('MODULE_PATH')) {
            define('MODULE_PATH', APP_PATH . '/../module');
        }
        $context = '';
        $pos = strrpos($_SERVER['SCRIPT_NAME'], '/');
        if ($pos > 0) {
            $context = substr($_SERVER['SCRIPT_NAME'], 0, $pos);
        }
        $this->appContext = $context;
    }

    /**
     * 启动应用
     *
     * @return void
     */
    public function startApp(): void
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
        $this->loadErrorHandler();
        $this->loadRouteErrorHandler();
        $this->loadRouteHandler();
        $this->invokeRoute();
    }

    /**
     * 加载错误处理器
     *
     * @return void
     */
    private function loadErrorHandler(): void
    {
        $errorHandlerClass = $this->config->getValue('ERROR_HANDLER');
        $errorHandler = new $errorHandlerClass();
        // 添加错误事件处理
        $this->addEventListener(ApplicationErrorEvent::class, [
            $errorHandler,
            'handleError'
        ]);
        // 当发生错误或者异常时,发送事件
        set_exception_handler(function ($exception) {
            $this->dispatchEvent(new ApplicationErrorEvent($exception));
        });
        set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) {
            $this->dispatchEvent(ApplicationErrorEvent::createCustom($errno, $errstr));
        });
    }

    /**
     * 加载路由
     *
     * @return void
     */
    private function loadRouteHandler(): void
    {
        $routeHandlerClass = $this->config->getValue('ROUTE_HANDLER');
        $routeHandler = new $routeHandlerClass();
        // 解析URL
        $url = '/';
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = $_SERVER['REQUEST_URI'];
        }
        $this->routeInfo = $routeHandler->getRouteInfo($url);
        $this->url = new UrlHelper($routeHandler, $this->appContext);
    }

    /**
     * 加载路由错误处理接口
     *
     * @return void
     */
    private function loadRouteErrorHandler(): void
    {
        $errorHandlerClass = $this->config->getValue('ROUTE_ERROR_HANDLER');
        $errorHandler = new $errorHandlerClass();
        // 添加错误事件处理
        $this->addEventListener(RouteErrorEvent::class, [
            $errorHandler,
            'handleError'
        ]);
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
        $controllerClass = $this->config->getValue('APP_NAMESPACE') . '\\module\\' . str_replace('.', '\\', $moduleName) . '\\controllers\\' . $controllerName;
        if (! class_exists($controllerClass)) {
            $event = RouteErrorEvent::createCustom(404, $moduleName . '/' . $controllerName . '/' . $actionName . '对应的控制器类' . $controllerClass . '不存在');
            $event->httpErrorCode = 404;
            $this->dispatchEvent($event);
            return;
        }
        $this->invokeController(new $controllerClass());
    }

    private function invokeController(Controller $controller): void
    {
        $moduleName = $this->routeInfo->moduleName;
        $controllerName = $this->routeInfo->controllerName;
        $actionName = $this->routeInfo->actionName;
        $controller->beforeAction($actionName);
        if (($moduleName != $this->routeInfo->moduleName) || ($controllerName != $this->routeInfo->controllerName)) {
            // 模块名或者控制器名变化
            $this->invokeRoute();
        } else {
            // 获取操作结果
            $actionMethodName = $this->routeInfo->actionName;
            $actionMethodPrefix = $this->config->getValue('ACTION_METHOD_PREFIX');
            if (! empty($actionMethodPrefix)) {
                $actionMethodName = $actionMethodPrefix . ucfirst($actionMethodName);
            }
            $actionResult = call_user_func([
                $controller,
                $actionMethodName
            ]);
            $this->invokeActionResult($actionResult);
        }
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

    /**
     * 调用控制器
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
}

