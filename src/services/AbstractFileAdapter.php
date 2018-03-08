<?php
namespace liuguang\mvc\services;

abstract class AbstractFileAdapter
{

    protected $pathPrefix = '';

    /**
     * 设置保存路径前缀
     *
     * @param string $pathPrefix
     *            路径前缀
     * @return void
     */
    public function setPathPrefix(string $pathPrefix): void
    {
        $this->pathPrefix = $pathPrefix;
    }

    /**
     * 获取保存路径前缀
     *
     * @return string
     */
    public function getPathPrefix(): string
    {
        return $this->pathPrefix;
    }

    /**
     * 获取文件object名称
     *
     * @param string $savePath
     *            保存路径
     * @return string
     */
    protected function getObjectName(string $savePath): string
    {
        $pathPrefix = $this->getPathPrefix();
        if ($pathPrefix != '') {
            return $pathPrefix . '/' . $savePath;
        }
        return $savePath;
    }

    /**
     * 保存文件
     *
     * @param string $tmpPath
     *            本地临时路径
     * @param string $savePath
     *            保存路径
     * @param string $contentType
     *            文件类型
     * @return void
     * @throws \Exception
     */
    public abstract function saveFile(string $tmpPath, string $savePath, ?string $contentType = null): void;

    /**
     * 保存文件内容
     *
     * @param string $savePath
     *            保存路径
     * @param string $content
     *            文件内容
     * @param string $contentType
     *            文件类型
     * @return void
     * @throws \Exception
     */
    public abstract function writeFile(string $savePath, string $content, ?string $contentType = null): void;

    /**
     * 读取文件内容
     *
     * @param string $savePath
     *            保存路径
     * @return string
     * @throws \Exception
     */
    public abstract function readFile(string $savePath): string;

    /**
     * 删除文件
     *
     * @param string $savePath
     *            保存路径
     * @return void
     * @throws \Exception
     */
    public abstract function deleteFile(string $savePath): void;

    /**
     * 获取文件的url地址
     *
     * @param string $savePath
     *            保存路径
     * @return string
     * @throws \Exception
     */
    public abstract function getFileUrl(string $savePath): string;
}