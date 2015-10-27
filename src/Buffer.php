<?php

namespace Fruit\Convas;

class Buffer
{
    private $capacity;
    private $buf; // array of array of point, point is array(char, color)

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

    private function grow($w, $h) {
        if ($this->capacity < $w) {
            $this->capacity = $w;
        }
        for ($i = count($this->buf); $i <= $h; $i++) {
            $this->buf[$i] = array();
        }
    }

    public function draw($x, $y, $str, Color $color = null)
    {
        if ($color == null) {
            $color = Color::NIL();
        }
        $this->grow($x + strlen($str), $y);

        for ($i = count($this->buf[$y]); $i < $x; $i++) {
            $this->buf[$y][$i] = array(' ', Color::NIL());
        }

        for ($i = 0; $i < strlen($str); $i++) {
            $this->buf[$y][$x+$i] = array(substr($str, $i, 1), $color);
        }
    }

    public function clear($x, $y, $w, $h)
    {
        for ($i = $y; $i < $y + $h; $i++) {
            $str = str_repeat(' ', $w);
            $this->draw($x, $i, $str);
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
