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
        $this->assertFalse($obj->getHasChanged());
        $dataArray['c'] = 6;
        $this->assertTrue($obj->getHasChanged());
    }

    public function test2()
    {
        $dataArray = [
            'a' => 1,
            'b' => 2
        ];
        $obj = new ObserverData($dataArray);
        $this->assertFalse($obj->getHasChanged());
        $dataArray['a'] = 6;
        $this->assertTrue($obj->getHasChanged());
        $dataArray['a'] = 1;
        $this->assertFalse($obj->getHasChanged());
    }

    public function test3()
    {
        $dataArray = [
            'a' => 1,
            'b' => 2
        ];
        $obj = new ObserverData($dataArray);
        $this->assertFalse($obj->getHasChanged());
        unset($dataArray['a']);
        unset($dataArray['b']);
        $this->assertTrue($obj->getHasChanged());
        $dataArray['b'] = 2;
        $dataArray['a'] = 1;
        $this->assertFalse($obj->getHasChanged());
    }
}

