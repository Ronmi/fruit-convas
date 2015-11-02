<?php

namespace FruitTest\Convas;

use PHPUnit_Framework_TestCase;
use Fruit\Convas\Buffer;
use Fruit\Convas\Color;

class BufferTest extends PHPUnit_Framework_TestCase
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
        $buf = new Buffer();
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
        $this->assertEquals("a  \033[1;32ma\033[0m \033[1;33;45ma\033[0m ", $actual);
        $buf->clear(0, 0, 7, 1);
        list($actual) = $buf->export(0, 0, 7, 1);
        $this->assertEquals("       ", $actual);
    }

    public function dgtP()
    {
        $graphChars = "+=-_\\|/><^VX*";
        $hChar = '=-_';
        $vChar = '|';
        $plusChar = $hChar . $vChar;
        $xChar = "X/\\";
        $arrowChar = '<>^V';
        $ret = array();
        // no change if old = new
        for ($i = 0; $i < strlen($graphChars); $i++) {
            $c = substr($graphChars, $i, 1);
            $ret[] = array($c, $c, $c);
        }

        $x = array(
            array("/", "\\", "X"), array("\\", "/", "X"),
            array('/', 'X', 'X'), array('X', '/', 'X',), array("\\", 'X', 'X'), array('X', "\\", 'X'),
        );

        $plus = array();
        for ($i = 0; $i < strlen($plusChar); $i++) {
            $c = substr($plusChar, $i, 1);
            $plus[] = array('+', $c, '+');
            $plus[] = array($c, '+', '+');
        }
        for ($i = 0; $i < strlen($hChar); $i++) {
            $c = substr($hChar, $i, 1);
            $plus[] = array($vChar, $c, '+');
            $plus[] = array($c, $vChar, '+');
        }
        $l = function($a, $b, $c) use (&$plus) {
            $plus[] = array($a, $b, $c);
            $plus[] = array($b, $a, $c);
        };
        $l('-', '_', '=');
        $l('-', '=', '=');
        $l('=', '_', '=');

        $star = array();
        $plusChar .= '+';
        for ($i = 0; $i < strlen($plusChar); $i++) {
            for ($j = 0; $j < strlen($xChar); $j++) {
                $c1 = substr($plusChar, $i, 1);
                $c2 = substr($xChar, $j, 1);
                $star[] = array($c1, $c2, '*');
                $star[] = array($c2, $c1, '*');
            }
        }
        for ($i = 0; $i < strlen($plusChar); $i++) {
            for ($j = 0; $j < strlen($arrowChar); $j++) {
                $c1 = substr($plusChar, $i, 1);
                $c2 = substr($arrowChar, $j, 1);
                $star[] = array($c1, $c2, $c2);
                $star[] = array($c2, $c1, $c2);
            }
        }
        for ($i = 0; $i < strlen($xChar); $i++) {
            for ($j = 0; $j < strlen($arrowChar); $j++) {
                $c1 = substr($xChar, $i, 1);
                $c2 = substr($arrowChar, $j, 1);
                $star[] = array($c1, $c2, $c2);
                $star[] = array($c2, $c1, $c2);
            }
        }
        $l = function($a, $b, $c) use (&$star) {
            $plus[] = array($a, $b, $c);
            $plus[] = array($b, $a, $c);
        };
        $l('>', '<', 'X');
        $l('>', '^', 'X');
        $l('>', 'V', 'X');
        $l('<', '^', 'X');
        $l('<', 'V', 'X');
        $l('^', 'V', 'X');
        $allChar = $arrowChar . $plusChar . $xChar;
        for ($i = 0; $i < strlen($allChar); $i++) {
            $l('*', substr($allChar, $i, 1), '*');
        }

        return array_merge($ret, $x, $plus, $star);
    }

    /**
     * @dataProvider dgtP
     */
    public function testDrawGraphTwice($old, $new, $expect)
    {
        $buf = new Buffer;
        $buf->draw(0, 0, $old);
        $buf->draw(0, 0, $new);

        list($actual) = $buf->export(0, 0, 1, 1);
        $this->assertEquals($expect, $actual);
    }

    /**
     * @dataProvider dgtP
     */
    public function testGraphOverwrite($old, $new, $expect)
    {
        $buf = new Buffer;
        $buf->draw(0, 0, $old);
        $buf->overwrite(0, 0, $new);

        list($actual) = $buf->export(0, 0, 1, 1);
        $this->assertEquals($new, $actual);
    }
}
