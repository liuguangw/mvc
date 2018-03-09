<?php
namespace liuguang\mvc\file;

use liuguang\mvc\services\AbstractFileAdapter;
use liuguang\mvc\Application;

/**
 * 本地文件存储适配器
 *
 * @author liuguang
 *        
 */
class LocalFileAdapter extends AbstractFileAdapter
{

    private $saveDir;

    private $httpContext;

    public function __construct(string $saveDir, string $httpContext, string $pathPrefix = '')
    {
        $this->saveDir = $saveDir;
        $this->httpContext = $httpContext;
        $this->pathPrefix = $pathPrefix;
    }

    /**
     * 工厂方法
     *
     * @param string $publicPath
     *            公共目录下目录名，如upload、upload/image
     * @param string $pathPrefix
     *            保存路径前缀
     * @return LocalFileAdapter
     */
    public static function createPublicInstance(string $publicPath, string $pathPrefix = ''): LocalFileAdapter
    {
        $saveDir = PUBLIC_PATH . '/./' . $publicPath;
        $httpContext = Application::$app->appContext . '/' . $publicPath;
        return new static($saveDir, $httpContext, $pathPrefix);
    }

    /**
     * 构建目标目录
     *
     * @param string $distPath
     *            目标文件路径
     * @return void
     */
    private function buildDistDir(string $distPath): void
    {
        $distDir = dirname($distPath);
        if (! is_dir($distDir)) {
            if (@mkdir($distDir, 0755, true) === false) {
                throw new \Exception('创建目录' . $distDir . '失败');
            }
        }
    }

    /**
     * 获取目标文件保存路径
     *
     * @param string $savePath            
     * @return string
     */
    private function getDistPath(string $savePath): string
    {
        return $this->saveDir . '/./' . $this->getObjectName($savePath);
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
        $distPath = $this->getDistPath($savePath);
        $this->buildDistDir($distPath);
        if (@copy($tmpPath, $distPath) === false) {
            throw new \Exception('copy文件到' . $distPath . '失败');
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractFileAdapter::writeFile()
     */
    public function writeFile(string $savePath, string $content, string $contentType = null): void
    {
        $distPath = $this->getDistPath($savePath);
        $this->buildDistDir($distPath);
        if (@file_put_contents($distPath, $content) === false) {
            throw new \Exception('写入文件到' . $distPath . '失败');
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
        $distPath = $this->getDistPath($savePath);
        if (! is_file($distPath)) {
            throw new \Exception('目标文件路径不存在');
        }
        $content = @file_get_contents($distPath);
        if ($content === false) {
            throw new \Exception('读取文件' . $distPath . '失败');
        }
        return $content;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\services\AbstractFileAdapter::deleteFile()
     */
    public function deleteFile(string $savePath): void
    {
        $distPath = $this->getDistPath($savePath);
        if (! is_file($distPath)) {
            return;
        }
        if (@unlink($distPath) === false) {
            throw new \Exception('删除文件' . $distPath . '失败');
        }
        // 删除空目录
        $distDir = realpath(dirname($distPath));
        $saveDir = realpath($this->saveDir);
        while ($distDir != $saveDir) {
            $files = scandir($distDir);
            $fileList = [];
            foreach ($files as $fileName) {
                if (($fileName != '.') && ($fileName != '..')) {
                    $fileList[] = $fileName;
                }
            }
            if (empty($fileList)) {
                @rmdir($distDir);
                $distDir = dirname($distDir);
            } else {
                $distDir = $this->saveDir;
            }
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
        return $this->httpContext . '/' . $this->getObjectName($savePath);
    }
}

