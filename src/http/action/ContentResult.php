<?php
namespace liuguang\mvc\http\action;

class ContentResult implements ActionResult
{

    /**
     * 内容
     * 
     * @var string
     */
    public $content = '';

    /**
     * 文档类型
     * 
     * @var string
     */
    public $contentType = 'text/html; charset=utf-8';

    /**
     * 状态码
     * 
     * @var integer
     */
    public $statusCode = 200;

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\http\action\ActionResult::executeResult()
     */
    public function executeResult(): void
    {
        http_response_code($this->statusCode);
        header('Content-Type: '.$this->contentType);
        echo $this->content;
    }
}

