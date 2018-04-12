<?php
use PHPUnit\Framework\TestCase;
use liuguang\mvc\data\ObserverData;

class ObserverTest extends TestCase
{

    public function test1()
    {
        $dataArray = [
            'a' => 1,
            'b' => 2
        ];
        $obj = new ObserverData($dataArray);
        $this->assertFalse($obj->hasChanged());
        $obj->setValue('a', 3);
        $this->assertTrue($obj->hasChanged());
    }

    public function test2()
    {
        $dataArray = [
            'a' => 1,
            'b' => 2
        ];
        $obj = new ObserverData($dataArray);
        $this->assertFalse($obj->hasChanged());
        $obj->setValue('a', 66);
        $this->assertTrue($obj->hasChanged());
        $obj->setValue('a', 1);
        $this->assertFalse($obj->hasChanged());
        $obj->remove('b');
        $this->assertTrue($obj->hasChanged());
        $obj->setValue('b', 2);
        $this->assertFalse($obj->hasChanged());
    }

    public function test3()
    {
        $dataArray = [
            'a' => 1,
            'b' => 2
        ];
        $obj = new ObserverData($dataArray);
        $this->assertFalse($obj->hasChanged());
        unset($dataArray['a']);
        $this->assertFalse($obj->hasChanged());
    }
}

