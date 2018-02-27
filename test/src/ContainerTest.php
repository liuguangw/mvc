<?php
use PHPUnit\Framework\TestCase;
use liuguang\mvc\Container;

class A1
{
}

class A2 extends A1
{
}

class A3 extends A1
{
}

/**
 * 容器测试
 *
 * @author liuguang
 *        
 */
class ContainerTest extends TestCase
{

    /**
     * 回调型
     */
    private function getCallableContainer(): Container
    {
        $container = new Container();
        $container->addCallableMap('A1', function () {
            return new A1();
        });
        return $container;
    }

    public function testCallable()
    {
        $container = $this->getCallableContainer();
        $this->assertInstanceOf('A1', $container->make('A1'));
    }

    private function getClassMapContainer(): Container
    {
        $container = new Container();
        $container->addClassMap('A1', 'A3', 'sss');
        return $container;
    }

    public function testClassMap()
    {
        $container = $this->getClassMapContainer();
        $obj = $container->make('A1');
        $this->assertInstanceOf('A1', $obj);
        $this->assertInstanceOf('A3', $obj);
        return $container;
    }

    /**
     * 测试缩略名
     *
     * @depends testClassMap
     */
    public function testShortName(Container $container)
    {
        $obj = $container->make('A1');
        $obj1 = $container->make('sss');
        $this->assertInstanceOf('A1', $obj1);
        $this->assertInstanceOf('A3', $obj1);
        $this->assertEquals(spl_object_hash($obj1), spl_object_hash($obj));
        $this->assertNotEquals(spl_object_hash($obj1), spl_object_hash($container->make('sss', false)));
    }

    private function getObjectMapContainer(): Container
    {
        $container = new Container();
        $a2Object = new A2();
        $a3Object = new A3();
        $container->addObjectMap('A1', $a2Object, '', 0);
        $container->addObjectMap('A1', $a3Object, '', 1);
        return $container;
    }

    public function testObjectMap()
    {
        $container = $this->getObjectMapContainer();
        $obj1 = $container->make('A1');
        $obj2 = $container->make('A1', true, 0);
        $obj3 = $container->make('A1', true, 1);
        $this->assertInstanceOf('A2', $obj1);
        $this->assertInstanceOf('A2', $obj2);
        $this->assertInstanceOf('A3', $obj3);
        $this->assertEquals(spl_object_hash($obj1), spl_object_hash($obj2));
    }
}

