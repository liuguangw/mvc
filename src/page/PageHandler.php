<?php
namespace liuguang\mvc\page;

interface PageHandler
{
    /**
     * 获取当前页码对应的URL地址
     * 
     * @param Paginator $paginator
     * @return string
     */
    public function getPageUrl(int $page): string;
    
    /**
     * 获取分页代码
     * 
     * @param Paginator $paginator
     * @return string
     */
    public function getHtml(Paginator $paginator): string;
}

