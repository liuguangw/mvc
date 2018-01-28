<?php
namespace liuguang\mvc\http\action;

use liuguang\mvc\Application;
use liuguang\mvc\data\DataMap;

/**
 * 跳转路由页面
 *
 * @author liuguang
 *        
 */
class RedirectToRouteResult extends ActionResult
{

    /**
     * 缺省路由
     *
     * @var string
     */
    public $route;

    /**
     *
     * @var DataMap
     */
    public $params;

    public function __construct(string $route, ?DataMap $params = null, bool $permanent = false)
    {
        $this->route = $route;
        $this->params = $params;
        $this->statusCode = $permanent ? 301 : 302;
        $this->contentType = '';
        $this->initExtraHeaders();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\http\action\ActionResult::outputContent()
     */
    protected function outputContent(): void
    {
        header('Location: ' . Application::$app->url->createUrl($this->route, $this->params));
    }
}

