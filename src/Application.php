<?php
namespace liuguang\mvc;

use liuguang\mvc\data\DataMap;

/**
 * 应用主类
 *
 * @author liuguang
 *        
 */
class Application
{

    private static $app = null;

    /**
     * mvc源代码(src)目录
     *
     * @var string
     */
    public $mvcSourcePath;

    /**
     *
     * @var DataMap
     */
    public $config;

    /**
     * 启动项目
     *
     * @param DataMap $config
     *            配置对象
     */
    public static function init(DataMap $config = null): void
    {
        if (self::$app === null) {
            $app = new static($config);
            self::$app = $app;
            $app->startApp();
        }
    }

    /**
     * 构造方法
     *
     * @param DataMap $config
     *            配置对象
     */
    private function __construct(DataMap $config = null)
    {
        $this->mvcSourcePath = __DIR__;
        $mvcConfig = DataMap::loadFromPhpFile($this->mvcSourcePath . '/../config.inc.php');
        if($config!==null){
            $mvcConfig->mergeData($config);
        }
        $this->config=$mvcConfig;
    }

    /**
     * @todo
     */
    private function startApp(): void
    {
        var_dump($this->config);
    }
}

