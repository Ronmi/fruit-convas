<?php

namespace FruitTest\Convas;

use PHPUnit_Framework_TestCase;
use Fruit\Convas\Buffer;
use Fruit\Convas\Color;

class SingletonTest extends PHPUnit_Framework_TestCase
{
    public function testCapacity()
    {
        $buf = new Buffer(20);
        $this->assertEquals(20, $buf->width());
    }

    public function testDrawAndWidth()
    {
        $buf = new Buffer;
        $buf->draw(0, 0, 'abc');
        $this->assertEquals(3, $buf->width());
        $buf->draw(20, 0, 'abc');
        $this->assertEquals(23, $buf->width());
    }

    public function testDrawAndHeight()
    {
        $buf = new Buffer;
        $buf->draw(0, 0, 'abc');
        $this->assertEquals(1, $buf->height());
        $buf->draw(0, 20, 'abc');
        $this->assertEquals(21, $buf->height());
    }

    public function testDrawAndExport()
    {
        $expect = 'hello, world';
        $buf = new Buffer;
        $buf->draw(0, 0, $expect);
        $this->assertEquals(1, $buf->height());

        $actual = $buf->export(0, 0, 12, 1);
        $this->assertEquals(array($expect), $actual);
    }

    public function testClear()
    {
        $buf = new Buffer(20);
        $buf->draw(0, 0, 'asd');
        $buf->clear(1, 0, 10, 2);
        $actual = $buf->export(0, 0, 10, 2);
        $this->assertEquals('a         ', $actual[0]);
        $this->assertEquals('          ', $actual[1]);
    }

    public function testColor()
    {
        $buf = new Buffer;
        $c1 = new Color(1, 32);
        $c2 = new Color(1, 33, 45);
        $buf->draw(0, 0, 'a');
        $buf->draw(3, 0, 'a', $c1);
        $buf->draw(5, 0, 'a', $c2);
        list($actual) = $buf->export(0, 0, 7, 1);
        $this->assertEquals("a  \033[1;32ma\033[m \033[1;33;45ma\033[m ", $actual);
        $buf->clear(0, 0, 7, 1);
        list($actual) = $buf->export(0, 0, 7, 1);
        $this->assertEquals("       ", $actual);
    }
}
