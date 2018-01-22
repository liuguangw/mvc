<?php
namespace liuguang\mvc\http\action;

/**
 * 跳转url
 *
 * @author liuguang
 *        
 */
class RedirectResult implements ActionResult
{

    /**
     * 是否为永久性转移
     *
     * @var bool
     */
    public $permanent = false;

    /**
     * 目标url
     *
     * @var string
     */
    public $url;

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\http\action\ActionResult::executeResult()
     */
    public function executeResult(): void
    {
        $statusCode = $this->permanent ? 301 : 302;
        http_response_code($statusCode);
        header('Location: ' . $this->url);
    }
}

