<?php
namespace liuguang\mvc\data;

class ObserverData
{

    public $orgData;

    public $addAttributes = [];

    public $updateAttributes = [];

    public $removeAttributes = [];

    public function __construct(array &$dataArray)
    {
        $this->orgData = $dataArray;
    }

    /**
     * 判断数据是否发生了变化
     *
     * @return bool
     */
    public function hasChanged(): bool
    {
        return ! (empty($this->addAttributes) && empty($this->updateAttributes) && empty($this->removeAttributes));
    }

    /**
     * 获取最终数组
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = $this->orgData;
        foreach ($this->addAttributes as $key => $value) {
            $result[$key] = $value;
        }
        foreach ($this->updateAttributes as $key => $value) {
            $result[$key] = $value;
        }
        foreach ($this->removeAttributes as $key => $value) {
            unset($result[$key]);
        }
        return $result;
    }

    /**
     * 判断是否存在指定的键
     *
     * @param string $key
     *            键名
     * @return bool
     */
    public function containsKey(string $key): bool
    {
        if (isset($this->orgData[$key])) {
            return ! isset($this->removeAttributes[$key]);
        } else {
            return isset($this->addAttributes[$key]);
        }
    }

    /**
     * 判断是否存在某个值
     *
     * @param mixed $value
     *            值
     * @return bool
     */
    public function containsValue($value): bool
    {
        return in_array($value, $this->toArray());
    }

    /**
     * 读取键值,或者不存在时默认值
     *
     * @param string $key
     *            键名
     * @param mixed $defaultValue
     *            当键名不存在时,返回的默认值
     * @return mixed
     */
    public function getValue(string $key, $defaultValue = null)
    {
        if (isset($this->orgData[$key])) {
            if (isset($this->removeAttributes[$key])) {
                // 已经被删除
                return $defaultValue;
            } elseif (isset($this->updateAttributes[$key])) {
                // 被修改
                return $this->updateAttributes[$key];
            }
            return $this->orgData[$key];
        } else {
            if (isset($this->addAttributes[$key])) {
                return $this->addAttributes[$key];
            }
            return $defaultValue;
        }
    }

    /**
     * 设置键值
     *
     * @param string $key
     *            键名
     * @param mixed $value
     *            键值
     * @return void
     */
    public function setValue(string $key, $value): void
    {
        if (isset($this->orgData[$key])) {
            if (isset($this->removeAttributes[$key])) {
                // 已经被删除
                unset($this->removeAttributes[$key]);
            }
            if ($this->orgData[$key] != $value) {
                $this->updateAttributes[$key] = $value;
            }elseif (isset($this->updateAttributes[$key])){
                unset($this->updateAttributes[$key]);
            }
        } else {
            $this->addAttributes[$key] = $value;
        }
    }

    /**
     * 删除键对应的映射关系
     *
     * @param string $key
     *            键名
     * @return void
     */
    public function remove(string $key): void
    {
        if (isset($this->orgData[$key])) {
            if (isset($this->updateAttributes[$key])) {
                unset($this->updateAttributes[$key]);
            }
        } else {
            if (isset($this->addAttributes[$key])) {
                unset($this->addAttributes[$key]);
            }
        }
        $this->removeAttributes[$key] = 0;
    }
}

