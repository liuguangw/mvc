<?php
namespace liuguang\mvc\file;

use liuguang\mvc\services\AbstractFileAdapter;
use Qcloud\Cos\Client;

class TencentCosAdapter extends AbstractFileAdapter
{

    /**
     *
     * @var Client
     */
    private $cosClient;

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
     *
     * @param string $secretId            
     * @param string $secretKey            
     * @param string $bucket            
     * @param string $webDomain            
     * @param string $region
     *            地区
     */
    public function __construct(string $secretId, string $secretKey, string $bucket, string $webDomain, string $region)
    {
        $config = [
            'region' => $region,
            'credentials' => [
                'secretId' => $secretId,
                'secretKey' => $secretKey
            ]
        ];
        $this->cosClient = new Client($config);
        $this->bucket = $bucket;
        $this->webDomain = $webDomain;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractFileAdapter::saveFile()
     */
    public function saveFile(string $tmpPath, string $savePath, string $contentType = null): void
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
    public function writeFile(string $savePath, string $content, string $contentType = null): void
    {
        if ($contentType === null) {
            $contentType = MimeHelper::getMimetype($savePath);
        }
        $this->cosClient->putObject([
            'Bucket' => $this->bucket,
            'Key' => $this->getObjectName($savePath),
            'Body' => $content
        ]);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractFileAdapter::readFile()
     */
    public function readFile(string $savePath): string
    {
        $result = $this->cosClient->getObject([
            'Bucket' => $this->bucket,
            'Key' => $this->getObjectName($savePath)
        ]);
        return $result['Body'];
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractFileAdapter::deleteFile()
     */
    public function deleteFile(string $savePath): void
    {
        $result = $this->cosClient->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $this->getObjectName($savePath)
        ]);
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

