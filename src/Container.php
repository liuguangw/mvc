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
     * 简称到类名/接口名的映射
     *
     * @var array
     */
    private $shortNameMap = [];

    /**
     * 类标识符
     *
     * @param string $classname
     *            类、接口名称
     * @param int $instanceId
     *            实现类别id
     * @return void
     */
    private function getFuncKey(string $classname, int $instanceId): string
    {
        return $classname . '#' . $instanceId;
    }

    /**
     * 添加[类名:回调]关系映射
     *
     * @param string $classname
     *            类、接口名称
     * @param callable $func
     *            回调
     * @param string $shortName
     *            简要标识
     * @param int $instanceId
     *            实现类别id
     * @return void
     */
    public function addCallableMap(string $classname, callable $func, string $shortName = '', int $instanceId = 0): void
    {
        $funcKey = $this->getFuncKey($classname, $instanceId);
        $this->classMap[$funcKey] = $func;
        if ($shortName != '') {
            $this->shortNameMap[$shortName] = $funcKey;
        }
    }

    /**
     * 添加[类名:类名]关系隐射
     *
     * @param string $classname
     *            类、接口名称
     * @param string $newClassname
     *            实现类的全名
     * @param string $shortName
     *            简要标识
     * @param int $instanceId
     *            实现类别id
     * @return void
     */
    public function addClassMap(string $classname, ?string $newClassname = null, string $shortName = '', int $instanceId = 0): void
    {
        if ($newClassname === null) {
            $newClassname = $classname;
        }
        $this->addCallableMap($classname, function (Container $container) use ($newClassname) {
            return new $newClassname();
        }, $shortName, $instanceId);
    }

    /**
     * 添加[类名:实例对象]关系隐射
     *
     * @param string $classname
     *            类、接口名称
     * @param object $classObject
     *            实例对象
     * @param string $shortName
     *            简要标识
     * @param int $instanceId
     *            实现类别id
     * @return void
     */
    public function addObjectMap(string $classname, $classObject, string $shortName = '', int $instanceId = 0): void
    {
        $this->addCallableMap($classname, function () use ($classObject) {
            return $classObject;
        }, $shortName, $instanceId);
    }

    /**
     * 获取对象
     *
     * @param string $name
     *            类、接口名称、简要标识
     * @param bool $isSingleton
     *            是否获取单例
     * @param int $instanceId
     *            实现类别id
     * @return object
     */
    public function make(string $name, bool $isSingleton = true, int $instanceId = 0)
    {
        // 获取标识
        if (isset($this->shortNameMap[$name])) {
            $funcKey = $this->shortNameMap[$name];
        } else {
            $funcKey = $this->getFuncKey($name, $instanceId);
        }
        // 非单例模式
        if (! $isSingleton) {
            return $this->getNewInstance($funcKey);
        }
        // 单例模式
        if (! isset($this->objectMap[$funcKey])) {
            $objectInstance = $this->getNewInstance($funcKey);
            $this->objectMap[$funcKey] = $objectInstance;
        }
        return $this->objectMap[$funcKey];
    }

    /**
     * 获取新实例
     *
     * @param string $funcKey
     *            标识
     * @return object
     * @throws \Exception
     */
    private function getNewInstance(string $funcKey)
    {
        if (! isset($this->classMap[$funcKey])) {
            throw new \UnexpectedValueException('找不到类' . $funcKey . '的实例规则');
        }
        return call_user_func($this->classMap[$funcKey], $this);
    }
}

