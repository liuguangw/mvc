<?php
namespace liuguang\mvc\file;

use liuguang\mvc\services\AbstractFileAdapter;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class QiniuFileAdapter extends AbstractFileAdapter
{

    /**
     *
     * @var Auth
     */
    private $auth;

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
     * @param string $accessKey
     *            accessKey
     * @param string $secretKey
     *            secretKey
     * @param string $bucket
     *            容器名称
     * @param string $webDomain
     *            外网url域名
     */
    public function __construct(string $accessKey, string $secretKey, string $bucket, string $webDomain)
    {
        $this->auth = new Auth($accessKey, $secretKey);
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
        // 生成上传Token
        $token = $this->auth->uploadToken($this->bucket);
        // 构建 UploadManager 对象
        $uploadMgr = new UploadManager();
        if ($contentType === null) {
            $contentType = MimeHelper::getMimetype($savePath);
        }
        list ($ret, $err) = $uploadMgr->putFile($token, $this->getObjectName($savePath), $tmpPath, null, $contentType);
        if ($err !== null) {
            throw new \Exception($err->message());
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractFileAdapter::writeFile()
     */
    public function writeFile(string $savePath, string $content, ?string $contentType = null): void
    {
        // 生成上传Token
        $token = $this->auth->uploadToken($this->bucket);
        // 构建 UploadManager 对象
        $uploadMgr = new UploadManager();
        if ($contentType === null) {
            $contentType = MimeHelper::getMimetype($savePath);
        }
        list ($ret, $err) = $uploadMgr->put($token, $this->getObjectName($savePath), $content, null, $contentType);
        if ($err !== null) {
            throw new \Exception($err->message());
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractFileAdapter::readFile()
     */
    public function readFile(string $savePath): string
    {
        return file_get_contents($this->getFileUrl($savePath));
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractFileAdapter::deleteFile()
     */
    public function deleteFile(string $savePath): void
    {
        $config = new \Qiniu\Config();
        $bucketManager = new \Qiniu\Storage\BucketManager($this->auth, $config);
        $err = $bucketManager->delete($this->bucket, $this->getObjectName($savePath));
        if ($err !== null) {
            throw new \Exception($err->message());
        }
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

