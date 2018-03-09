<?php
namespace liuguang\mvc\data;

/**
 * 字符串封装
 *
 * @author liuguang
 *        
 */
class RawString
{

    /**
     *
     * @var string
     */
    private $str;

    public function __construct(string $str)
    {
        $this->str = $str;
    }

    public function __toString()
    {
        return $this->str;
    }
}

