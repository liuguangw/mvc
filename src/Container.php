<?php
namespace liuguang\mvc;

/**
 * IOC容器类
 *
 * @author liuguang
 *        
 */
class Container
{

    /**
     * 关系规则隐射
     *
     * @var array
     */
    private $classMap = [];

    /**
     * 类实例映射
     *
     * @var array
     */
    private $objectMap = [];

    /**
     * 添加[类名:回调]关系隐射
     *
     * @param string $classname
     *            类、接口名称
     * @param callable $func
     *            回调
     * @param string $key
     *            别名
     */
    public function addCallableMap(string $classname, callable $func, string $key = '0'): void
    {
        if (! isset($this->classMap[$classname])) {
            $this->classMap[$classname] = [];
        }
        $this->classMap[$classname][$key] = $func;
    }

    /**
     * 添加[类名:类名]关系隐射
     *
     * @param string $classname
     *            类、接口名称
     * @param callable $func
     *            回调
     * @param string $key
     *            别名
     */
    public function addClassMap(string $classname, ?string $newClassname = null, string $key = '0'): void
    {
        if ($newClassname === null) {
            $newClassname = $classname;
        }
        $this->addCallableMap($classname, function (Container $container) use ($newClassname) {
            return new $newClassname;
        });
    }

    /**
     * 添加[类名:实例对象]关系隐射
     *
     * @param string $classname
     *            类、接口名称
     * @param callable $func
     *            回调
     * @param string $key
     *            别名
     */
    public function addObjectMap(string $classname, $classObject, string $key = '0'): void
    {
        $this->addCallableMap($classname, function () use ($classObject) {
            return $classObject;
        });
    }

    /**
     * 获取新实例
     *
     * @param string $classname
     *            类、接口名称
     * @param string $key
     *            别名
     * @return object
     */
    public function getNewInstance(string $classname, string $key = '0')
    {
        if (! isset($this->classMap[$classname][$key])) {
            throw new \UnexpectedValueException('找不到类' . $classname . '的实例规则');
        }
        $func = $this->classMap[$classname][$key];
        return call_user_func($func, $this);
    }

    /**
     * 获取单例实例
     *
     * @param string $classname
     *            类、接口名称
     * @param string $key
     *            别名
     * @return object
     */
    public function getInstance(string $classname, string $key = '0')
    {
        if (! isset($this->objectMap[$classname][$key])) {
            $objectInstance = $this->getNewInstance($classname, $key);
            $this->objectMap[$classname][$key] = $objectInstance;
        }
        return $this->objectMap[$classname][$key];
    }
}

