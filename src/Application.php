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

    /**
     * 应用实例
     * 
     * @var Application
     */
    public static $app = null;

    /**
     * mvc源代码(src)目录
     *
     * @var string
     */
    public $mvcSourcePath;

    /**
     * 应用配置对象
     *
     * @var DataMap
     */
    public $config=null;

    public function __construct()
    {
        $this->mvcSourcePath = __DIR__;
    }

    /**
     * 启动应用
     * 
     * @return void
     */
    public function startApp(): void
    {
        if(self::$app!==null){
            return ;
        }
        self::$app = $this;
        if(!defined('APP_PATH')){
            exit('APP_PATH is not defined !');
        }
        if(!defined('APP_CONFIG_PATH')){
            define('APP_CONFIG_PATH', APP_PATH.'/./config');
        }
        //加载框架配置文件
        $config=DataMap::loadFromPhpFile($this->mvcSourcePath.'/../config.inc.php');
        //应用配置
        if($this->config===null){
            $appConfigFile=APP_CONFIG_PATH.'/./config.inc.php';
            if(is_file($appConfigFile)){
                $appConfig=DataMap::loadFromPhpFile($appConfigFile);
                $config->mergeData($appConfig);
            }
        }else{
            $config->mergeData($this->config);
        }
        $this->config=$config;
        $this->loadErrorHandler();
        $this->loadRouteHandler();
        //@todo
    }
    
    /**
     * 加载错误处理器
     * 
     * @return void
     */
    private function loadErrorHandler():void{
        
    }
    
    /**
     * 加载路由
     *
     * @return void
     */
    private function loadRouteHandler():void{
        
    }
}

