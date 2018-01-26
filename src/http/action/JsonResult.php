<?php
namespace liuguang\mvc\http\action;

/**
 * json响应
 *
 * @author liuguang
 *        
 */
class JsonResult extends ActionResult
{

    /**
     * 内容
     *
     * @var array
     */
    public $data = [];

    /**
     *
     * @see json_encode
     * @var integer
     */
    public $encodeOptions = 0;

    /**
     * 状态码
     *
     * @var integer
     */
    public $statusCode = 200;

    public function __construct(array $data, int $encodeOptions = 0)
    {
        $this->contentType = 'application/json; charset=utf-8';
        $this->data = $data;
        $this->encodeOptions = $encodeOptions;
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
        echo json_encode($this->data, $this->encodeOptions);
    }
}

