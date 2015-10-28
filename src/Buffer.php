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

    private static $CJKBlocks = array(
        array(0x1100, 0x11FF), // Hangul Jamo
        /*
          array(0x2e00, 0x2e7f), // Supplemental Punctuation
          array(0x2e80, 0x2eff), // CJK Radicals Supplement
          array(0x2f00, 0x2fdf), // Kangxi Radicals
          array(0x2ff0, 0x2fff), // Ideographic Description Characters
          array(0x3000, 0x303f), // CJK Symbols and Punctuation
          array(0x3040, 0x309f), // Hiragana
          array(0x30a0, 0x30ff), // Katakana
          array(0x3100, 0x312f), // Bopomofo
          array(0x3130, 0x318f), // Hangul Compatibility Jamo
          array(0x3190, 0x319f), // Kanbun
          array(0x31a0, 0x31bf), // Bopomofo Extended
          array(0x31c0, 0x31ef), // CJK Strokes
          array(0x31f0, 0x31ff), // Katakana Phonetic Extensions
          array(0x3200, 0x32ff), // Enclosed CJK Letters and Months
          array(0x3300, 0x33ff), // CJK Compatibility
          array(0x3400, 0x4dbf), // CJK Unified Ideographs Extension A
          array(0x4dc0, 0x4dff), // Yijing Hexagrams Symbols
          array(0x4e00, 0x9fff), // CJK Unified Ideographs
        */
        array(0x2e00, 0x9fff),
        /*
          array(0xac00, 0xd7af), // Hangul Syllables
          array(0xd7b0, 0xd7ff), // Hangul Jamo Extended-B
        */
        array(0xac00, 0xd7ff),
        array(0xf900, 0xfaff), // CJK Compatibility Ideographs
        array(0xf1e0, 0xfe1f), // Vertical Forms
        array(0xfe30, 0xfe4f), // CJK Compatibility Forms
        array(0xfe00, 0xfe60), // https://en.wikipedia.org/wiki/Halfwidth_and_fullwidth_forms#In_Unicode
        array(0xffe0, 0xffef), // https://en.wikipedia.org/wiki/Halfwidth_and_fullwidth_forms#In_Unicode
    );

    private static function isWide($char)
    {
        // fast macthing ASCII
        if (strlen($char) == 1) {
            return false;
        }

        // fast maching most CJK characters
        if (preg_match('/\p{Han}|\p{Katakana}|\p{Hiragana}|\p{Hangul}|\p{Bopomofo}/u', $char)) {
            return true;
        }

        // many thanks to http://php.net/manual/en/function.ord.php#109812
        $offset = 0;
        $code = ord(substr($char, $offset, 1));
        if ($code >= 128) {        //otherwise 0xxxxxxx
            if ($code < 224) $bytesnumber = 2;                //110xxxxx
            else if ($code < 240) $bytesnumber = 3;        //1110xxxx
            else if ($code < 248) $bytesnumber = 4;    //11110xxx
            $codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
            for ($i = 2; $i <= $bytesnumber; $i++) {
                $offset ++;
                $code2 = ord(substr($char, $offset, 1)) - 128;        //10xxxxxx
                $codetemp = $codetemp*64 + $code2;
            }
            $code = $codetemp;
        }

        // fast matching some of non-CJK characters
        if ($code < self::$CJKBlocks[0][0]) {
            return false;
        }
        
        foreach (self::$CJKBlocks as $blocks) {
            if ($code >= $blocks[0] and $code <= $blocks[1]) {
                return true;
            }
        }
        return false;
    }
    
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

        for ($fix = 0, $i = 0; $i < mb_strlen($str); $i++) {
            $char = mb_substr($str, $i, 1);
            if (self::isWide($char)) {
                $fix++;
            }
            $this->drawChar($x+$i+$fix, $y, $char, $color);
        }
    }

    public function overwrite($x, $y, $str, Color $color = null)
    {
        if ($color == null) {
            $color = Color::NIL();
        }

        $this->extend($x, $y, $str);

        for ($fix = 0, $i = 0; $i < mb_strlen($str); $i++) {
            $char = mb_substr($str, $i, 1);
            if (self::isWide($char)) {
                $fix++;
            }
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
            if (self::isWide($char)) {
                $i++;
            }
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

    public function exportAll()
    {
        return $this->export(0, 0, $this->capacity, count($this->buf));
    }
}
