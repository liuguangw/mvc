<?php
return [
    'ERROR_HANDLER' => 'liuguang\mvc\ErrorHandler',
    'ROUTE_HANDLER' => 'liuguang\mvc\http\DefaultRouteHandler',
    'ROUTE_ERROR_HANDLER' => 'liuguang\mvc\ErrorHandler',
    'APP_NAMESPACE' => 'app',
    'ACTION_METHOD_PREFIX' => 'action',
    'ERROR_HANDLER_SHOW_SOURCE' => true, // 默认的错误处理器是否展示源码
    'VIEW_PATH' => dirname(APP_PATH) . DIRECTORY_SEPARATOR . 'view',
    'DISABLE_TPL_CACHE' => true
];