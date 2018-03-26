<?php
namespace liuguang\mvc\http\action;

class AjaxResult extends JsonResult
{

    /**
     * 构造方法
     *
     * @param bool $success
     *            是否成功
     * @param string $message
     *            错误消息
     * @param mixed $data
     *            数据
     */
    public function __construct(bool $success, ?string $message, $data = null)
    {
        if ($success) {
            $data = [
                'code' => 0,
                'data' => $data
            ];
        } else {
            $data = [
                'code' => - 1,
                'message' => $message
            ];
        }
        parent::__construct($data);
    }

    /**
     * 操作成功时响应
     *
     * @param mixed $data            
     * @return AjaxResult
     */
    public static function successResult($data = null): AjaxResult
    {
        return new static(true, null, $data);
    }

    /**
     * 操作失败时响应
     *
     * @param string $message            
     * @return AjaxResult
     */
    public static function errorResult(string $message): AjaxResult
    {
        return new static(false, $message);
    }

    /**
     * 异常响应
     *
     * @param \Exception $exception            
     * @return AjaxResult
     */
    public static function exceptionResult(\Exception $exception): AjaxResult
    {
        return static::errorResult($exception->getMessage());
    }
}

