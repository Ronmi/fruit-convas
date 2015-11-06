<?php

namespace FruitTest\Convas;

use PHPUnit_Framework_TestCase;
use Fruit\Convas\WString;

class WStringTest extends PHPUnit_Framework_TestCase
{
    public function stringP()
    {
        return array(
            array("a", 1),
            array("0", 1),
            array("=", 1),
            array("爽", 2),
            array("ㄝ", 2),
            array("サ", 2),
            array("럴", 2),
            array("︶", 2),
        );
    }

    /**
     * @dataProvider stringP
     */
    public function testIsWide($char, $len)
    {
        $expect = $len == 2;
        $actual = WString::isWide($char);

        $this->assertEquals($expect, $actual, $char);
    }

    /**
     * @dataProvider stringP
     */
    public function testStringWidth($char, $len)
    {
        $actual = WString::stringWidth($char);

        $this->assertEquals($len, $actual, $char);
    }

    public function testWordWrap()
    {
        $text = "On the day Adobe patched two of the Flash Player zero-day vulnerabilities uncovered following the Hacking Team breach, FireEye researchers noticed that, one of the flaws had been used in an attack aimed at organizations in Japan.\nItaly-based surveillance software company Hacking Team has suffered a data breach and hackers leaked a total of 400GB of data stolen from the spyware 看山山很小 maker’s systems. Researchers discovered exploits for several unpatched vulnerabilities after analyzing the Hacking Team leak, including Windows kernel, Microsoft Office, and Adobe Flash Player exploits.";
        $expect = array(
            'On the day Adobe patched two of the',
            'Flash Player zero-day vulnerabilities',
            'uncovered following the Hacking Team',
            'breach, FireEye researchers noticed',
            'that, one of the flaws had been used in',
            'an attack aimed at organizations in',
            'Japan.',
            'Italy-based surveillance software',
            'company Hacking Team has suffered a data',
            'breach and hackers leaked a total of',
            '400GB of data stolen from the spyware 看',
            '山山很小 maker’s systems. Researchers',
            'discovered exploits for several',
            'unpatched vulnerabilities after',
            'analyzing the Hacking Team leak,',
            'including Windows kernel, Microsoft',
            'Office, and Adobe Flash Player exploits.',
        );

        $actual = WString::wordWrap($text, 40);

        $this->assertEquals($expect, $actual);
    }
}
