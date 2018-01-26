<?php
namespace liuguang\mvc\http\action;

/**
 * 跳转url
 *
 * @author liuguang
 *        
 */
class RedirectResult extends ActionResult
{

    /**
     * 目标url
     *
     * @var string
     */
    public $url;

    public function __construct(string $url, bool $permanent = false)
    {
        $this->url = $url;
        $this->statusCode = $permanent ? 301 : 302;
        $this->contentType='';
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
        header('Location: ' . $this->url);
    }
}

