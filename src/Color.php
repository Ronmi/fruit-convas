<?php

namespace Fruit\Convas;

class Color
{
    private $mod;
    private $bg;
    private $fg;

    public static function NIL()
    {
        static $nil = null;
        if ($nil == null) {
            $nil = new Color(0, 0, 0);
        }
        return $nil;
    }

    public function __construct($modifier, $fg = 0, $bg = 0)
    {
        $this->mod = ($modifier < 0) ? 0 : $modifier;
        $this->bg = $bg;
        $this->fg = $fg;
    }

    public function export()
    {
        if ($this->mod == 0 and $this->fg <= 0 and $this->bg <= 0) {
            return "\033[0m";
        }
        if ($this->fg <= 0 and $this->bg <= 0) {
            return sprintf("\033[%dm", $this->mod);
        }
        if ($this->fg <= 0) {
            return sprintf("\033[%d;%dm", $this->mod, $this->bg);
        }
        if ($this->bg <= 0) {
            return sprintf("\033[%d;%dm", $this->mod, $this->fg);
        }
        return sprintf("\033[%d;%d;%dm", $this->mod, $this->fg, $this->bg);
    }
}
