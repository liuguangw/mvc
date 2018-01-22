<?php
namespace liuguang\mvc\http\action;

/**
 * json响应
 * 
 * @author liuguang
 *
 */
class JsonResult implements ActionResult
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
     * 文档类型
     *
     * @var string
     */
    public $contentType = 'application/json; charset=utf-8';

    /**
     * 状态码
     *
     * @var integer
     */
    public $statusCode = 200;

    private function outputJson(array $data)
    {
        http_response_code($this->statusCode);
        header('Content-Type: ' . $this->contentType);
        echo json_encode($data, $this->encodeOptions);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\http\action\ActionResult::executeResult()
     */
    public function executeResult(): void
    {
        $this->outputJson($this->data);
    }
}

