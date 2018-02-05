<?php
namespace liuguang\mvc\services;

use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;

/**
 * url助手
 *
 * @author liuguang
 *        
 */
abstract class UrlAsset
{

    const NOT_LOADED = 0;

    const ONE_PACKAGE = 1;

    const MUTI_PACKAGES = 2;

    /**
     * 包状态
     *
     * @var int
     */
    protected $status = 0;

    /**
     *
     * @var Packages
     */
    protected $packages;

    /**
     *
     * @var Package
     */
    protected $package;

    /**
     * 获取默认的package
     *
     * @return Package
     */
    public abstract function getDefaultPackage(): Package;

    public abstract function getNamedPackages(): array;

    /**
     * 获取url
     *
     * @param string $path
     *            路径
     * @param string $packageName
     *            包名称
     * @return string
     */
    public function getUrl(string $path, ?string $packageName = null): string
    {
        if ($this->status == self::NOT_LOADED) {
            $namedPackages = $this->getNamedPackages();
            if (empty($namedPackages)) {
                $this->package = $this->getDefaultPackage();
                $this->status = self::ONE_PACKAGE;
            } else {
                $defaultPackage = $this->getDefaultPackage();
                $this->packages = new Packages($defaultPackage, $namedPackages);
                $this->status = self::MUTI_PACKAGES;
            }
        }
        if ($this->status == self::ONE_PACKAGE) {
            return $this->package->getUrl($path);
        } else {
            return $this->packages->getUrl($path, $packageName);
        }
    }
}