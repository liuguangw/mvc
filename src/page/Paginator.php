<?php
namespace liuguang\mvc\page;

use liuguang\mvc\Application;

/**
 * 分页类
 *
 * @author liuguang
 *        
 */
class Paginator
{

    /**
     * 当前页码
     *
     * @var int
     */
    public $currentPage;

    /**
     * 总页码
     *
     * @var int
     */
    public $totalPage;

    /**
     * 每页显示条数
     *
     * @var int
     */
    public $pageSize;

    /**
     * 总条数
     *
     * @var int
     */
    public $infoCount;

    /**
     * 构造方法
     *
     * @param int $pageSize
     *            每页显示条数
     * @param int $infoCount
     *            总条数
     * @param int $currentPage
     *            当前页码
     */
    public function __construct(int $pageSize, int $infoCount, int $currentPage = 1)
    {
        if ($currentPage <= 0) {
            $currentPage = 1;
        }
        $this->currentPage = $currentPage;
        if ($pageSize <= 0) {
            throw new \Exception('pageSize ' . $pageSize . ' is not valid');
        }
        $this->pageSize = $pageSize;
        if ($infoCount <= 0) {
            $this->totalPage = 0;
        } else {
            $this->totalPage = ceil($infoCount / $pageSize);
        }
        $this->infoCount = $infoCount;
    }

    /**
     * 获取分页handler
     *
     * @return PageHandler
     */
    private function getPageHandler(): PageHandler
    {
        return Application::$app->getService('pageHandler');
    }

    /**
     * 获取分页代码
     *
     * @return string
     */
    public function getHtml(): string
    {
        return $this->getPageHandler()->getHtml($this);
    }
}