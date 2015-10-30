<?php

namespace Fruit\Convas;

class WString
{
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

    public static function isWide($char)
    {
        $len = strlen($char);
        // fast macthing ASCII
        if ($len == 1) {
            return false;
        }

        // fast maching most CJK characters
        if (preg_match('/\p{Han}|\p{Katakana}|\p{Hiragana}|\p{Hangul}|\p{Bopomofo}/u', $char)) {
            return true;
        }

        // many thanks to http://php.net/manual/en/function.ord.php#109812
        $code = ord(substr($char, 0, 1));
        if ($code >= 128) {        //otherwise 0xxxxxxx
            $codetemp = $code - 192;
            if ($code < 240) {
                $codetemp -= 32;
            } elseif ($code < 248) {
                $codetemp -= 16;
            }
            for ($i = 1; $i < $len; $i++) {
                $code2 = ord(substr($char, $i, 1)) - 128;        //10xxxxxx
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

    public static function stringWidth($str)
    {
        $len = mb_strlen($str);
        $fix = 0;
        for ($i = 0; $i < $len; $i++) {
            if (self::isWide(mb_substr($str), $i, 1)) {
                $fix ++;
            }
        }
        return $len + $fix;
    }
}
