<?php
return [
    'ERROR_HANDLER' => 'liuguang\mvc\ErrorHandler',
    'ROUTE_HANDLER' => 'liuguang\mvc\http\DefaultRouteHandler',
    'ROUTE_ERROR_HANDLER' => 'liuguang\mvc\ErrorHandler',
    'URL_ASSET' => 'liuguang\mvc\http\DefaultUrlAsset',
    'STATIC_URL_VERSION' => 'V1',
    'APP_NAMESPACE' => 'app',
    'ACTION_METHOD_PREFIX' => 'action',
    'ERROR_HANDLER_SHOW_SOURCE' => true, // 默认的错误处理器是否展示源码
    'DISABLE_TPL_CACHE' => true,
    'DEFAULT_EXTRA_HEADERS' => [
        'X-Powered-By: liuguang/mvc 1.0'
    ]
];