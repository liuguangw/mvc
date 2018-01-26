<?php
namespace liuguang\mvc\http\action;

/**
 * 内容响应
 *
 * @author liuguang
 *        
 */
class ContentResult extends ActionResult
{

    /**
     * 内容
     *
     * @var string
     */
    public $content = '';

    public function __construct(string $content)
    {
        $this->content = $content;
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
        echo $this->content;
    }
}

