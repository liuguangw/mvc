<?php
namespace liuguang\mvc\http;

use liuguang\mvc\Application;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;

class DefaultUrlAsset extends UrlAsset
{

    /**
     * 默认版本
     *
     * @var string
     */
    private $version;

    public function __construct()
    {
        $this->version = Application::$app->config->getValue('STATIC_URL_VERSION', 'v' . date('Ym'));
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\http\UrlAsset::getDefaultPackage()
     */
    public function getDefaultPackage(): Package
    {
        return new PathPackage(Application::$app->appContext, new StaticVersionStrategy($this->version));
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\http\UrlAsset::getNamedPackages()
     */
    public function getNamedPackages(): array
    {
        return [];
    }
}

