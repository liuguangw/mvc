<?php
namespace liuguang\mvc\file;

use liuguang\mvc\services\AbstractFileAdapter;
use OSS\OssClient;

class AliyunOssAdapter extends AbstractFileAdapter
{

    /**
     * oss对象
     *
     * @var OssClient
     */
    private $ossClient;

    /**
     * bucket名称
     *
     * @var string
     */
    private $bucket;

    /**
     * 外网url域名
     *
     * @var string
     */
    private $webDomain;

    /**
     * 构造方法
     *
     * @param string $accessKeyId
     *            从OSS获得的AccessKeyId
     * @param string $accessKeySecret
     *            从OSS获得的AccessKeySecret
     * @param string $endpoint
     *            OSS数据中心访问域名，例如http://oss-cn-hangzhou.aliyuncs.com
     * @param string $bucket
     *            容器名称
     * @param string $webDomain
     *            外网url域名
     */
    public function __construct(string $accessKeyId, string $accessKeySecret, string $endpoint, string $bucket, string $webDomain)
    {
        $this->ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $this->bucket = $bucket;
        $this->webDomain = $webDomain;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractFileAdapter::saveFile()
     */
    public function saveFile(string $tmpPath, string $savePath, ?string $contentType = null): void
    {
        if (! is_file($tmpPath)) {
            throw new \Exception('文件' . $tmpPath . '不存在');
        }
        $content = @file_get_contents($tmpPath);
        if ($content === false) {
            throw new \Exception('读取文件' . $tmpPath . '失败');
        }
        $this->writeFile($savePath, $content, $contentType);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractFileAdapter::writeFile()
     */
    public function writeFile(string $savePath, string $content, ?string $contentType = null): void
    {
        if ($contentType === null) {
            $contentType = MimeHelper::getMimetype($savePath);
        }
        $options = [
            OssClient::OSS_CONTENT_TYPE => $contentType
        ];
        $this->ossClient->putObject($this->bucket, $this->getObjectName($savePath), $content, $options);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractFileAdapter::readFile()
     */
    public function readFile(string $savePath): string
    {
        return $this->ossClient->getObject($this->bucket, $this->getObjectName($savePath));
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractFileAdapter::deleteFile()
     */
    public function deleteFile(string $savePath): void
    {
        return $this->ossClient->deleteObject($this->bucket, $this->getObjectName($savePath));
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractFileAdapter::getFileUrl()
     */
    public function getFileUrl(string $savePath): string
    {
        return 'http://' . $this->webDomain . '/' . $this->getObjectName($savePath);
    }
}

