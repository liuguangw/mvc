<?php
namespace liuguang\mvc\data;

use liuguang\mvc\CoreException;

class DataMap implements \Iterator, \ArrayAccess
{

    private $data;

    private $position;

    private $pointerKeys;

    public function __construct(array & $dataArray)
    {
        $this->data = &$dataArray;
    }

    // 数组式访问
    /**
     *
     * {@inheritdoc}
     *
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }
        return null;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    // 迭代
    
    /**
     *
     * {@inheritdoc}
     *
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->position = 0;
        $this->pointerKeys = array_keys($this->data);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Iterator::valid()
     */
    public function valid()
    {
        return array_key_exists($this->pointerIndex, $this->pointerKeys);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->pointerKeys[$this->pointerIndex];
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Iterator::current()
     */
    public function current()
    {
        return $this->data[$this->key()];
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Iterator::next()
     */
    public function next()
    {
        $this->pointerIndex ++;
    }

    /**
     * 清理映射
     *
     * @return void
     */
    public function clear(): void
    {
        $this->data = [];
        $this->rewind();
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
        return $this->offsetExists($key);
    }

    /**
     * 判断是否存在某个键值
     *
     * @param mixed $value
     *            键值
     * @return bool
     */
    public function containsValue($value): bool
    {
        return in_array($value, $this->data);
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
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        } else {
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
        $this->offsetSet($key, $value);
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
        $this->offsetUnset($key);
    }

    /**
     * 返回键值对个数
     *
     * @return int
     */
    public function size(): int
    {
        return count($this->data);
    }

    /**
     * 导出键值对
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * 将数据进行合并
     *
     * @param array $arr
     *            数据数组
     * @return DataMap
     */
    public function mergeArray(array $arr): DataMap
    {
        $this->data = array_merge($this->data, $arr);
        return $this;
    }

    /**
     * 将数据进行合并
     *
     * @param DataMap $data
     *            数据
     * @return DataMap
     */
    public function mergeData(DataMap $data): DataMap
    {
        return $this->mergeArray($data->toArray());
    }

    /**
     * 从php文件加载数据
     *
     * @param string $filePath            
     * @throws CoreException
     * @return DataMap
     */
    public static function loadFromPhpFile(string $filePath): DataMap
    {
        if (! is_file($filePath)) {
            throw new CoreException(CoreException::FILE_NOT_FOUND, '找不到php文件: ' . $filePath, $filePath);
        }
        $data = include $filePath;
        return new static($data);
    }

    /**
     * 初始化一个新的空映射
     *
     * @return DataMap
     */
    public static function getNewInstance(): DataMap
    {
        $data = [];
        return new DataMap($data);
    }
}

