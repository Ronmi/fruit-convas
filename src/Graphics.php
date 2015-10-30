<?php

namespace Fruit\Convas;

class Graphics
{
    private $buf;
    private $xOrig;
    private $yOrig;
    private $color;
    public $overwrite; // enable overwrite mode

    public function __construct(Buffer $buf)
    {
        $this->buf = $buf;
        $this->color = Color::NIL();
    }

    public function getWidth()
    {
        return $this->buf->width() - $this->xOrig;
    }

    public function setColor(Color $c)
    {
        $this->color = $c;
        return $this;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function transit($x, $y)
    {
        $this->xOrig += $x;
        $this->yOrig += $y;
        return $this;
    }

    public function origin()
    {
        return array($this->xOrig, $this->yOrig);
    }

    private function trans($x, $y)
    {
        return array($x + $this->xOrig, $y + $this->yOrig);
    }

    private function doDraw($x, $y, $str, Color $color)
    {
        if ($this->overwrite) {
            $this->buf->overwrite($x, $y, $str, $color);
        } else {
            $this->buf->draw($x, $y, $str, $color);
        }

        return $this;
    }

    public function drawString($x, $y, $str)
    {
        list($x, $y) = $this->trans($x, $y);
        if ($y < 0 or $x + strlen($str) < 0) {
            // position of whole string is at left of buffer, so no need to draw
            return $this;
        }

        if ($x < 0) {
            // only some part of string in the buffer, strip the unseen part
            $str = substr($str, -$x);
            $x = 0;
        }

        $this->doDraw($x, $y, $str, $this->color);
        return $this;
    }

    private function drawHorizontalThinLine($x1, $y1, $x2, $y2, $slope)
    {
        if ($x2 < $x1) {
            list($x1, $x2) = array($x2, $x1);
            list($y1, $y2) = array($y2, $y1);
        }

        $y = $y1;
        for (; $x1 <= $x2; $x1++) {
            if ($x1 < 0) {
                $y += $slope;
                continue;
            }
            $yReal = floor($y);
            $delta = $y - $yReal;
            if ($delta >= 5.5/9.0) {
                $yReal++;
            }
            if ($yReal < 0) {
                $y += $slope;
                continue;
            }
            $line = '-';
            if ($delta >= 2.5/9.0 and $delta < 5.5/9.0) {
                $line = '_';
            }
            $this->doDraw($x1, $yReal, $line, $this->color);
            $y += $slope;
        }
    }

    private function drawVerticalThinLine($x1, $y1, $x2, $y2)
    {
        $slope = ($x2 - $x1) / ($y2 - $y1);
        if ($y2 < $y1) {
            list($x1, $x2) = array($x2, $x1);
            list($y1, $y2) = array($y2, $y1);
        }

        $x = $x1;
        for (; $y1 <= $y2; $y1++) {
            if ($y1 < 0) {
                $x += $slope;
                continue;
            }
            $xReal = floor($x);
            $delta = $x - $xReal;
            if ($delta > 0.5) {
                $xReal++;
            }
            if ($xReal < 0) {
                $x += $slope;
                continue;
            }
            $this->doDraw($xReal, $y1, '|', $this->color);
            $x += $slope;
        }
    }

    private function drawOtherThinLine($x1, $y1, $x2, $y2)
    {
        $slope = ($x2 - $x1) / ($y2 - $y1);
        $line = '/';
        if ($slope > 0) {
            $line = "\\";
        }
        if ($y2 < $y1) {
            list($x1, $x2) = array($x2, $x1);
            list($y1, $y2) = array($y2, $y1);
        }

        $x = $x1;
        for (; $y1 <= $y2; $y1++) {
            if ($y1 < 0) {
                $x += $slope;
                continue;
            }
            $xReal = floor($x);
            $delta = $x - $xReal;
            if ($delta > 0.5) {
                $xReal++;
            }
            if ($xReal < 0) {
                $x += $slope;
                continue;
            }
            $this->doDraw($xReal, $y1, $line, $this->color);
            $x += $slope;
        }
    }

    /**
     * Draw a thin line with -_|\/
     */
    public function drawLine($x1, $y1, $x2, $y2)
    {
        list($x1, $y1) = $this->trans($x1, $y1);
        list($x2, $y2) = $this->trans($x2, $y2);
        if ($x1 == $x2) {
            // draw vertical straight line
            if ($y2 < $y1) {
                list($y1, $y2) = array($y2, $y1);
            }

            for (; $y1 <= $y2; $y1++) {
                $this->doDraw($x1, $y1, '|', $this->color);
            }
            return $this;
        }

        $slope = ($y2 - $y1) / ($x2 - $x1);
        if ($slope <= 0.5 and $slope >= -0.5) {
            $this->drawHorizontalThinLine($x1, $y1, $x2, $y2, $slope);
        } elseif ($slope > 5 or $slope < -5) {
            $this->drawVerticalThinLine($x1, $y1, $x2, $y2);
        } else {
            $this->drawOtherThinLine($x1, $y1, $x2, $y2);
        }
        return $this;
    }

    public function drawEllipse($x1, $y1, $x2, $y2)
    {
        if ($x1 > $x2) {
            list($x1, $x2) = array($x2, $x1);
        }
        if ($y1 > $y2) {
            list($y1, $y2) = array($y2, $y1);
        }
        list($x1, $y1) = $this->trans($x1, $y1);
        list($x2, $y2) = $this->trans($x2, $y2);

        $theta = 0;
        $step = \M_PI / (($x2-$x1) * 2 + ($y2-$y1) * 2);
        $r = ($x2 - $x1) / 2;
        $r2 = ($y2 - $y1) / ($x2 - $x1) * $r;
        $h = ($x2 + $x1) / 2;
        $k = ($y2 + $y1) / 2;

        while ($theta < \M_PI*2) {
            $x = round($h + $r * cos($theta));
            $y = round($k + $r2 * sin($theta));

            $line = '-';
            if ($theta > deg2rad(25) and $theta < deg2rad(70)) {
                $line = "/";
            } elseif ($theta > deg2rad(205) and $theta < deg2rad(250)) {
                $line = "/";
            } elseif ($theta > deg2rad(115) and $theta < deg2rad(160)) {
                $line = "\\";
            } elseif ($theta > deg2rad(295) and $theta < deg2rad(340)) {
                $line = "\\";
            } elseif ($theta <= deg2rad(25) or $theta >= deg2rad(340)) {
                $line = '|';
            } elseif ($theta <= deg2rad(205) and $theta >= deg2rad(160)) {
                $line = '|';
            }

            $this->doDraw($x, $y, $line, $this->color);

            $theta += $step;
        }
        return $this;
    }
}
