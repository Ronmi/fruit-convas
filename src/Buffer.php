<?php

namespace Fruit\Convas;

class Buffer
{
    private $capacity;
    private $buf; // array of array of point, point is array(char, color)
    private static $graphChars = array(
        '/' => true,
        "\\" => true,
        '|' => true,
        '-' => true,
        '=' => true,
        '_' => true,
        '+' => true,
        'X' => true,
        '*' => true,
        '<' => true,
        '>' => true,
        'V' => true,
        '^' => true,
    );

    public function __construct($capacity = 0)
    {
        $this->capacity = $capacity;
        $this->buf = array(array(array(' ', Color::NIL())));
    }

    public function width()
    {
        return $this->capacity;
    }

    public function height()
    {
        return count($this->buf);
    }

    private function grow($w, $h)
    {
        if ($this->capacity < $w) {
            $this->capacity = $w;
        }
        for ($i = count($this->buf); $i <= $h; $i++) {
            $this->buf[$i] = array(array(' ', Color::NIL()));
        }
    }

    private static function mergeGraphChar($old, $char)
    {
        if ($old == $char) {
            return $old;
        }
        $star = 0; // *
        $x = 0; // X / \
        $plus = 0; // + | - _ =
        $arrow = 0; // < > V ^
        $arrowChar = '';

        foreach (array($old, $char) as $c) {
            switch ($c) {
            case '*':
                $star++;
                break;
            case 'X':
            case '/':
            case "\\":
                $x++;
                break;
            case '+':
            case '|':
            case '-':
            case '_':
            case '=':
                $plus++;
                break;
            default:
                $arrow++;
                $arrowChar = $c;
                break;
            }
        }

        if ($star > 0) {
            return '*';
        }

        if ($x == 2) {
            return 'X';
        }

        if ($plus == 2) {
            if ($old == '+' or $char == '+') {
                return '+';
            }
            if ($old == '|' or $char == '|') {
                return '+';
            }
            return '=';
        }

        if ($x + $plus == 2) {
            return '*';
        }

        if ($arrow == 2) {
            return 'X';
        }

        if ($arrow == 1) {
            return $arrowChar;
        }

        return '*';
    }

    private function drawChar($x, $y, $char, Color $color)
    {
        list($oldChar) = $this->buf[$y][$x];

        if ($oldChar == ' ') {
        } elseif (isset(self::$graphChars[$oldChar])) {
            $char = self::mergeGraphChar($oldChar, $char);
        } else { // image or text
            $char = $oldChar;
        }
        $this->buf[$y][$x] = array($char, $color);
    }

    private function extend($x, $y, $str)
    {
        $this->grow($x + strlen($str), $y);

        $sz = mb_strlen($str) + $x;
        for ($i = count($this->buf[$y]); $i < $sz; $i++) {
            $this->buf[$y][$i] = array(' ', Color::NIL());
        }
    }

    public function draw($x, $y, $str, Color $color = null)
    {
        if ($color == null) {
            $color = Color::NIL();
        }

        $this->extend($x, $y, $str);

        for ($i = 0; $i < mb_strlen($str); $i++) {
            $char = mb_substr($str, $i, 1);
            $this->drawChar($x+$i, $y, $char, $color);
        }
    }

    public function overwrite($x, $y, $str, Color $color = null)
    {
        if ($color == null) {
            $color = Color::NIL();
        }

        $this->extend($x, $y, $str);

        for ($i = 0; $i < mb_strlen($str); $i++) {
            $char = mb_substr($str, $i, 1);
            $this->buf[$y][$x+$i] = array($char, $color);
        }
    }

    public function clear($x, $y, $w, $h)
    {
        $this->grow($x+$w, $y+$h);
        for ($i = $y; $i < $y + $h; $i++) {
            for ($j = count($this->buf[$y]); $j < $x; $j++) {
                $this->buf[$i][$j] = array(' ', Color::NIL());
            }
            for ($j = $x; $j < $x+$w; $j++) {
                $this->buf[$i][$j] = array(' ', Color::NIL());
            }
        }
    }

    private function exportRow($row, $x, $w)
    {
        $color = Color::NIL();
        $ret = '';
        $sz = count($this->buf[$row]);
        for ($i = $x;$i < $x+$w and $i < $sz; $i++) {
            list($char, $clr) = $this->buf[$row][$i];
            if ($color != $clr) {
                $ret .= $clr->export();
                $color = $clr;
            }
            $ret .= $char;
        }
        if ($x+$w > $sz) {
            if ($color != Color::NIL()) {
                $ret .= Color::NIL()->export();
            }
            $ret .= str_repeat(' ', $x+$w-$sz);
        }
        return $ret;
    }

    public function export($x, $y, $w, $h)
    {
        $this->grow($x+$w, $y+$h);

        $ret = array();
        for ($i = $y; $i < $y+$h; $i++) {
            $ret[] = $this->exportRow($i, $x, $w);
        }

        return $ret;
    }
}
